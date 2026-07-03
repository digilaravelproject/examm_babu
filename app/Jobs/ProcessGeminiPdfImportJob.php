<?php

namespace App\Jobs;

use App\Services\AiImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessGeminiPdfImportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 1800; // 30 minutes
    public $tries = 3;      // Allow retries for quota issues
    public $backoff = [90, 180]; // Wait 1.5 then 3 minutes between retries (Gemini says retry in ~53s)

    protected $batchId;
    protected $pdfPath;
    protected $topicId;
    protected $userId;
    protected $startPage;
    protected $endPage;

    /**
     * Create a new job instance.
     */
    public function __construct($batchId, $pdfPath, $topicId, $userId, $startPage = 1, $endPage = 50)
    {
        $this->batchId = $batchId;
        $this->pdfPath = $pdfPath;
        $this->topicId = $topicId;
        $this->userId = $userId;
        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    /**
     * Execute the job.
     */
    public function handle(AiImportService $aiService): void
    {
        $batch = \App\Models\AiImportBatch::find($this->batchId);
        if (!$batch) {
            Log::error("Batch not found for Job: {$this->batchId}");
            return;
        }

        try {
            Log::info("Starting AI Question Extraction for Batch: {$this->batchId}", [
                'topic_id' => $this->topicId,
                'pages' => "{$this->startPage}-{$this->endPage}",
                'job_id' => $this->job->getJobId() ?? 'unknown'
            ]);

            // 1. Mark as Processing
            $this->updateStatus('processing', 'Analyzing PDF document...', 10);

            // 2. Call AI Service
            $this->updateStatus('processing', 'Uploading to Gemini File API...', 30);

            $questions = $aiService->callGeminiApi(
                Storage::disk('local')->path($this->pdfPath),
                $this->startPage,
                $this->endPage,
                $this->batchId,
                $this->topicId,
                function (int $percent, string $message) {
                    $this->updateStatus('processing', $message, $percent);
                }
            );
            $diagnostics = $aiService->getLastImportDiagnostics();

            // 3. Store raw result for verification phase
            $questionsJsonPath = 'temp/ai_batch_' . $this->batchId . '_questions.json';
            $existing = [];
            if (Storage::exists($questionsJsonPath)) {
                $existing = json_decode(Storage::get($questionsJsonPath), true) ?? [];
            }
            $allQuestions = array_merge($existing, $questions);
            Storage::put($questionsJsonPath, json_encode($allQuestions));

            // Update processed pages count in metadata
            $metadata = $batch->metadata ?? [];
            $processedPages = ($metadata['processed_pages'] ?? 0) + ($this->endPage - $this->startPage + 1);
            $metadata['processed_pages'] = $processedPages;
            
            // Do NOT mark as completed here, let FinalizeGeminiPdfImportJob do it.
            $batch->update([
                'metadata' => $metadata
            ]);
            
            // Calculate global progress
            $totalPages = max(1, (int)($metadata['total_pages'] ?? 1));
            $globalProgress = min(95, round(($processedPages / $totalPages) * 95));
            $this->updateStatus('processing', "Processed pages up to {$this->endPage}...", $globalProgress, count($allQuestions));

            Log::info("AI Extraction chunk finished for Batch: {$this->batchId}", [
                'chunk' => "{$this->startPage}-{$this->endPage}",
                'count' => count($questions)
            ]);
        } catch (\Throwable $e) {
            Log::error("ProcessGeminiPdfImportJob Exception [Batch: {$this->batchId}]: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'batch_id' => $this->batchId
            ]);
            $this->handleFailure($e);
            if (str_contains($e->getMessage(), 'Import incomplete')) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Update batch status in both Database and Cache for real-time polling.
     */
    private function updateStatus(string $status, string $message, int $progress, int $count = 0): void
    {
        $batch = \App\Models\AiImportBatch::find($this->batchId);
        if ($batch) {
            $batch->update([
                'status' => $status,
                'message' => \Illuminate\Support\Str::limit($message, 250),
                'progress' => $progress,
                'questions_count' => $count ?: $batch->questions_count
            ]);

            Cache::put("ai_import_status_{$this->batchId}", [
                'status' => $status,
                'message' => \Illuminate\Support\Str::limit($message, 250),
                'progress' => $progress,
                'questions_count' => $count ?: ($batch->questions_count ?? 0)
            ], 3600); // 1 hour expiration
        }
    }

    /**
     * Centralized failure handler.
     */
    private function handleFailure(\Throwable $e): void
    {
        $errorMessage = $e->getMessage();
        $isQuota = str_contains($errorMessage, '429') || str_contains($errorMessage, 'Quota') || str_contains($errorMessage, 'limit');
        $isIncomplete = str_contains($errorMessage, 'Import incomplete');
        
        if ($isQuota) {
            $errorMessage = "AI Quota exceeded. System will automatically retry in a moment...";
        } elseif ($isIncomplete) {
            $errorMessage = \Illuminate\Support\Str::limit($errorMessage, 500);
        }

        $this->updateStatus($isQuota ? 'processing' : 'failed', $errorMessage, $isQuota ? 50 : 0);

        $batch = \App\Models\AiImportBatch::find($this->batchId);
        if ($batch) {
            $metadata = $batch->metadata ?? [];
            $metadata['failure_type'] = $isIncomplete ? 'incomplete_extraction' : 'error';
            $metadata['failed_at'] = now()->toDateTimeString();

            $batch->update([
                'error_details' => $e->getMessage(),
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Handle a job failure (Final failure after all retries).
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("AI Import Job PERMANENTLY Failed [Batch {$this->batchId}]", [
            'error' => $exception->getMessage(),
            'batch_id' => $this->batchId
        ]);

        $this->handleFailure($exception);

        // Cleanup on permanent failure
        if (Storage::exists($this->pdfPath)) {
            Storage::delete($this->pdfPath);
        }
    }
}
