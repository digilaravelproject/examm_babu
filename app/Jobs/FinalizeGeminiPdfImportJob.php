<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\AiImportBatch;

class FinalizeGeminiPdfImportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes max for finalization
    public $tries = 3;

    protected $batchId;
    protected $pdfPath;

    /**
     * Create a new job instance.
     */
    public function __construct($batchId, $pdfPath)
    {
        $this->batchId = $batchId;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batch = AiImportBatch::find($this->batchId);
        if (!$batch) {
            Log::error("Batch not found for Finalize Job: {$this->batchId}");
            return;
        }

        try {
            Log::info("Finalizing AI Question Extraction for Batch: {$this->batchId}");

            $questionsJsonPath = 'temp/ai_batch_' . $this->batchId . '_questions.json';
            $questions = [];
            
            if (Storage::exists($questionsJsonPath)) {
                $questions = json_decode(Storage::get($questionsJsonPath), true) ?? [];
            }

            // Mark as Completed
            $this->updateStatus('completed', 'AI Extraction Complete! Found ' . count($questions) . ' potential questions.', 100, count($questions));
            
            $batch->update([
                'metadata' => array_merge($batch->metadata ?? [], [
                    'final_extracted_count' => count($questions),
                ]),
            ]);

            Log::info("AI Extraction Completely Finished for Batch: {$this->batchId}", [
                'count' => count($questions)
            ]);

            // DO NOT delete the PDF here. The frontend UI needs to download it to crop images using pdf.js.
            // Cleanup will be handled by a scheduled command for old files.
            // if (Storage::exists($this->pdfPath)) {
            //     Storage::delete($this->pdfPath);
            // }
            Log::error("FinalizeGeminiPdfImportJob Exception [Batch: {$this->batchId}]: " . $e->getMessage());
            $this->updateStatus('failed', 'Error during finalization: ' . $e->getMessage(), 0);
            throw $e;
        }
    }

    /**
     * Update batch status in both Database and Cache.
     */
    private function updateStatus(string $status, string $message, int $progress, int $count = 0): void
    {
        $batch = AiImportBatch::find($this->batchId);
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
            ], 3600);
        }
    }
}
