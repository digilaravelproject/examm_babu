<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\AiImportBatch;
use App\Services\AiImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Settings\AiSettings;

class AiImportController extends Controller
{
    protected $aiService;
    protected $settings;

    /**
     * @param AiImportService $aiService
     * @param AiSettings $settings
     */
    public function __construct(AiImportService $aiService, AiSettings $settings)
    {
        $this->aiService = $aiService;
        $this->settings = $settings;
    }

    /**
     * Display the AI Import interface.
     */
    public function index()
    {
        $topics = Topic::with(['skill.microCategory.subCategory'])
            ->orderBy('name')
            ->get();

        $recentBatches = AiImportBatch::where('user_id', Auth::id())
            ->with('topic:id,name')
            ->latest()
            ->limit(5)
            ->get();
            
        $activeModel = ($this->settings->model_name === 'custom') 
            ? $this->settings->custom_model 
            : ($this->settings->model_name ?: 'gemini-1.5-flash');

        return view('admin.ai-import.index', compact('topics', 'recentBatches', 'activeModel'));
    }

    /**
     * STEP 1: Upload PDF and process via AI in the background.
     */
    public function uploadAndProcess(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'pdf_file' => 'nullable|mimes:pdf|max:51200', // 50MB
            'batch_id' => 'nullable|string',
            'start_page' => 'nullable|integer|min:1',
            'end_page' => 'nullable|integer|min:1',
            'total_pages' => 'nullable|integer|min:1',
        ]);

        try {
            $topicId = $request->input('topic_id');
            $userId = Auth::id();
            $totalPages = (int) $request->input('total_pages', 0);
            $startPage = (int) $request->input('start_page', 1);

            // Smart end_page: use user input > actual PDF pages > safe fallback
            if ($request->filled('end_page')) {
                $endPage = (int) $request->input('end_page');
            } elseif ($totalPages > 0) {
                $endPage = $totalPages;
            } else {
                $endPage = 50; // Safe fallback instead of 999
            }

            // Never exceed actual PDF page count if we know it
            if ($totalPages > 0 && $endPage > $totalPages) {
                $endPage = $totalPages;
            }

            if ($request->filled('batch_id')) {
                $batchId = $request->input('batch_id');
                $batch = AiImportBatch::find($batchId);
                
                if (!$batch) {
                    return response()->json(['success' => false, 'message' => 'Session expired or invalid batch ID.'], 404);
                }
                $pdfPath = $batch->pdf_path;
            } else {
                if (!$request->hasFile('pdf_file')) {
                    return response()->json(['success' => false, 'message' => 'PDF file is required for new import.'], 422);
                }

                $batchId = Str::random(20);
                $pdfFile = $request->file('pdf_file');
                $pdfPath = $pdfFile->storeAs('temp', 'ai_import_' . $batchId . '.pdf');

                $batch = AiImportBatch::create([
                    'id' => $batchId,
                    'topic_id' => $topicId,
                    'user_id' => $userId,
                    'pdf_path' => $pdfPath,
                    'start_page' => $startPage,
                    'end_page' => $endPage,
                    'status' => 'pending',
                    'message' => 'Queuing PDF for AI extraction...',
                    'metadata' => [
                        'start_time' => Carbon::now('Asia/Kolkata')->format('d-M-Y h:i:s A'),
                        'original_filename' => $pdfFile->getClientOriginalName()
                    ]
                ]);
            }

            // Set initial status in cache for fast polling
            Cache::put("ai_import_status_{$batchId}", [
                'status' => 'pending',
                'message' => 'Queuing PDF for AI extraction...',
                'progress' => 0
            ], 3600);

            // Dispatch Background Job
            \App\Jobs\ProcessGeminiPdfImportJob::dispatch(
                $batchId, 
                $pdfPath, 
                (int)$topicId, 
                (int)$userId, 
                (int)$startPage, 
                (int)$endPage
            );

            Log::info("AI Import Batch Created", [
                'batch_id' => $batchId,
                'user_id' => $userId,
                'topic_id' => $topicId,
                'file' => $batch->metadata['original_filename'] ?? 'unknown'
            ]);

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'status' => 'pending',
                'message' => 'Processing started in background.',
                'start_time' => $batch->metadata['start_time'] ?? '--',
            ]);
        } catch (\Exception $e) {
            Log::error('AI Import Upload Error', [
                'user_id' => Auth::id(),
                'topic_id' => $request->input('topic_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * STEP 2: Check the status of background processing.
     */
    public function checkStatus($batchId)
    {
        $status = Cache::get("ai_import_status_{$batchId}");

        if (!$status) {
            // Fallback to Database if cache expired
            $batch = AiImportBatch::find($batchId);
            if ($batch) {
                $status = [
                    'status' => $batch->status,
                    'message' => $batch->message,
                    'progress' => $batch->progress,
                    'questions_count' => $batch->questions_count
                ];
            } else {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Status not found or expired.'
                ]);
            }
        }

        // If completed, check if file exists
        if ($status['status'] === 'completed') {
            $questionsPath = 'temp/ai_batch_' . $batchId . '_questions.json';
            if (!Storage::exists($questionsPath)) {
                $status['status'] = 'failed';
                $status['message'] = 'Extraction failed: Result file missing.';
                
                // Sync to DB
                AiImportBatch::where('id', $batchId)->update([
                    'status' => 'failed',
                    'message' => $status['message']
                ]);
            }
        }

        return response()->json($status);
    }

    /**
     * Download the PDF file for a batch (used for client-side processing).
     */
    public function downloadPdf($batchId)
    {
        $batch = AiImportBatch::findOrFail($batchId);
        
        // Security: Check ownership
        if ((int)$batch->user_id !== (int)Auth::id()) {
            abort(403, 'Unauthorized batch access.');
        }

        if (!Storage::exists($batch->pdf_path)) {
            abort(404, 'PDF file not found.');
        }

        return Storage::response($batch->pdf_path);
    }

    /**
     * Update the raw JSON questions data for a batch.
     */
    public function updateQuestions(Request $request, $batchId)
    {
        $request->validate([
            'questions' => 'required|array'
        ]);

        $filePath = "temp/ai_batch_{$batchId}_questions.json";
        if (!Storage::exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Batch not found.']);
        }

        $questions = array_map(function ($question) {
            return is_array($question)
                ? $this->aiService->normalizeCorrectAnswerFields($question)
                : $question;
        }, $request->input('questions', []));

        Storage::put($filePath, json_encode($questions));

        return response()->json(['success' => true, 'message' => 'JSON updated successfully.']);
    }

    /**
     * Handle image extraction (cropping) from frontend.
     */
    public function uploadCroppedImage(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'question_index' => 'required|integer',
            'image_base64' => 'required|string',
            'image_type' => 'nullable|string',
        ]);

        $batchId = $request->input('batch_id');
        $qIndex = (int) $request->input('question_index');
        $imageType = (string) $request->input('image_type', 'question');

        $batch = AiImportBatch::find($batchId);
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!$batch || !Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Session expired or invalid batch.'], 404);
        }

        // Security: Check ownership
        if ((int)$batch->user_id !== (int)Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized batch access.'], 403);
        }

        try {
            // Encode Base64 to public storage
            $imageBase64 = (string) $request->input('image_base64');

            if (!str_contains($imageBase64, ',')) {
                return response()->json(['success' => false, 'message' => 'Invalid image format.'], 422);
            }

            $imageParts = explode(';base64,', $imageBase64);
            $decodedImage = base64_decode($imageParts[1]);
            
            if (!$decodedImage) {
                return response()->json(['success' => false, 'message' => 'Failed to decode image.'], 422);
            }

            $extension = str_contains($imageParts[0], 'png') ? 'png' : 'jpg';

            $dir = 'ai_extracted/' . $batchId;
            Storage::disk('public')->makeDirectory($dir);

            $fileName = $dir . '/img_' . Str::random(8) . '_' . $qIndex . '.' . $extension;

            /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
            $publicDisk = Storage::disk('public');
            $stored = $publicDisk->put($fileName, $decodedImage);

            if (!$stored || !$publicDisk->exists($fileName)) {
                Log::error('Cropped image write verification failed', [
                    'batch_id' => $batchId,
                    'question_index' => $qIndex,
                    'image_type' => $imageType,
                    'path' => $fileName,
                ]);
                return response()->json(['success' => false, 'message' => 'Failed to store cropped image.'], 500);
            }
            
            $imageUrl = $this->verifiedPublicStorageUrl($fileName);

            // Thread-safe update using Cache Lock
            $lock = Cache::lock('ai_sync_' . $batchId, 10);
            $lock->block(5);

            try {
                $content = Storage::get($jsonFile);
                $questions = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
                    throw new \Exception('Failed to read or parse questions JSON for update.');
                }

                if (isset($questions[$qIndex])) {
                    $imgHtml = '<img src="' . e($imageUrl) . '" class="ai-img rounded shadow-sm max-w-full my-2" />';

                    $before = $questions;
                    $questions = $this->aiService->attachImageHtmlToQuestion($questions, $qIndex, $imageType, $imgHtml);
                    if ($before === $questions) {
                        Log::warning('Cropped image could not be attached to target field', [
                            'batch_id' => $batchId,
                            'question_index' => $qIndex,
                            'image_type' => $imageType,
                        ]);
                    }
                    Storage::put($jsonFile, json_encode($questions));
                }
            } finally {
                $lock->release();
            }

            return response()->json(['success' => true, 'image_url' => $imageUrl, 'image_html' => $imgHtml]);
        } catch (\Exception $e) {
            Log::error('Cropped Image Upload Error: ' . $e->getMessage(), [
                'batch_id' => $batchId,
                'question_index' => $qIndex,
                'image_type' => $imageType,
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function verifiedPublicStorageUrl(string $fileName): string
    {
        $publicPath = public_path('storage/' . $fileName);

        if (!file_exists($publicPath)) {
            Log::warning('Public storage path not directly visible; falling back to request-root URL', [
                'public_path' => $publicPath,
                'storage_path' => storage_path('app/public/' . $fileName),
            ]);
        }

        return rtrim(request()->root(), '/') . '/storage/' . ltrim($fileName, '/');
    }

    public function logImageCropFailure(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'failures' => 'required|array',
        ]);

        /** @var AiImportBatch|null $batch */
        $batchId = $request->input('batch_id');
        $failures = $request->input('failures', []);

        $batch = AiImportBatch::where('id', $batchId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Invalid batch.'], 404);
        }

        Log::warning('AI import image crop failures reported by browser', [
            'batch_id' => $batchId,
            'failures' => $failures,
        ]);

        $metadata = $batch->metadata ?? [];
        $metadata['image_crop_failures'] = array_merge($metadata['image_crop_failures'] ?? [], $failures);
        $batch->update(['metadata' => $metadata]);

        return response()->json(['success' => true]);
    }

    /**
     * Preview extracted data.
     */
    public function preview(Request $request, $batchId)
    {
        $batch = AiImportBatch::find($batchId);
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!Storage::exists($jsonFile)) {
            if ($request->ajax() || $request->has('json')) {
                return response()->json(['success' => false, 'message' => 'Session expired.'], 404);
            }
            return redirect()->route('admin.ai-import.index')->with('error', 'Session expired.');
        }

        // Security: Check ownership
        if ($batch) {
            if ((int)$batch->user_id !== (int)Auth::id()) {
                abort(403, 'Unauthorized batch access.');
            }
        }

        $questions = json_decode(Storage::get($jsonFile), true) ?? [];
        $questions = array_map(function ($question) {
            return is_array($question)
                ? $this->aiService->normalizeCorrectAnswerFields($question)
                : $question;
        }, $questions);

        if ($request->ajax() || $request->has('json')) {
            return response()->json($questions);
        }

        return view('admin.ai-import.preview', compact('questions', 'batchId'));
    }

    /**
     * Approve and persist data to DB.
     */
    public function approve(Request $request, $batchId)
    {
        $batch = AiImportBatch::find($batchId);
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!$batch || !Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Required data missing.']);
        }

        try {
            // Security: Check ownership
            if ((int)$batch->user_id !== (int)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized batch access.'], 403);
            }

            $questions = json_decode(Storage::get($jsonFile), true) ?? [];

            // Persistence handled by Service
            $count = $this->aiService->importQuestions($questions, (int)$batch->topic_id, (int)Auth::id());

            // Post-success Cleanup
            if (Storage::exists($batch->pdf_path)) {
                Storage::delete($batch->pdf_path);
            }
            Storage::delete($jsonFile);
            
            // Mark as finished in DB (Optional: delete the batch record or keep it)
            $batch->update([
                'status' => 'completed',
                'progress' => 100,
                'questions_count' => $count,
                'message' => 'Imported successfully.'
            ]);

            return response()->json([
                'success' => true, 
                'message' => "Import successful: {$count} questions added.",
                'redirect' => route('admin.ai-import.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Approve Import Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Persistence Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel and cleanup.
     */
    public function cancelImport(Request $request)
    {
        $batchId = $request->input('batch_id');
        
        Log::info("Attempting to cancel AI Import session", ['batch_id' => $batchId, 'user_id' => Auth::id()]);

        try {
            /** @var AiImportBatch|null $batch */
            $batch = AiImportBatch::where('id', $batchId)
                ->where('user_id', Auth::id())
                ->first();
            
            // 1. Cleanup Public Assets (extracted images)
            if (Storage::disk('public')->exists('ai_extracted/' . $batchId)) {
                Storage::disk('public')->deleteDirectory('ai_extracted/' . $batchId);
            }
            
            // 2. Cleanup Temporary Files (JSON questions)
            Storage::delete('temp/ai_batch_' . $batchId . '_questions.json');
            
            // 3. Cleanup PDF and Record
            if ($batch) {
                if ($batch->pdf_path) {
                    Storage::delete($batch->pdf_path);
                }
                $batch->delete();
            } else {
                // If batch not in DB, try to delete the default PDF path if it exists
                Storage::delete('temp/ai_import_' . $batchId . '.pdf');
            }
            
            // 4. Cleanup Cache
            Cache::forget("ai_import_status_{$batchId}");
            
            Log::info("AI Import session cancelled successfully", ['batch_id' => $batchId]);
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error("Failed to cancel AI Import session", [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Cleanup failed: ' . $e->getMessage()], 500);
        }
    }
}
