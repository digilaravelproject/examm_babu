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
    public $backoff = [60, 120]; // Wait 1 then 2 minutes between retries

    protected $batchId;
    protected $pdfPath;
    protected $topicId;
    protected $userId;
    protected $startPage;
    protected $endPage;

    /**
     * Create a new job instance.
     */
    public function __construct($batchId, $pdfPath, $topicId, $userId, $startPage = 1, $endPage = 999)
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
                $this->topicId
            );

            // 3. Store raw result for verification phase
            $this->updateStatus('processing', 'Formatting extracted questions...', 80);
            $questionsJsonPath = 'temp/ai_batch_' . $this->batchId . '_questions.json';
            Storage::put($questionsJsonPath, json_encode($questions));

            // 4. Mark as Completed (Ready for review)
            $this->updateStatus('completed', 'AI Extraction Complete! Found ' . count($questions) . ' potential questions.', 100, count($questions));

            Log::info("AI Extraction Finished for Batch: {$this->batchId}", ['count' => count($questions)]);
        } catch (\Throwable $e) {
            Log::error("ProcessGeminiPdfImportJob Exception [Batch: {$this->batchId}]: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'batch_id' => $this->batchId
            ]);
            $this->handleFailure($e);
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
        
        if ($isQuota) {
            $errorMessage = "AI Quota exceeded. System will automatically retry in a moment...";
        }

        $this->updateStatus($isQuota ? 'processing' : 'failed', $errorMessage, $isQuota ? 50 : 0);

        $batch = \App\Models\AiImportBatch::find($this->batchId);
        if ($batch) {
            $batch->update(['error_details' => $e->getMessage()]);
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
