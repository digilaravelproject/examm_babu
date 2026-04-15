<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Services\AiImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiImportController extends Controller
{
    protected $aiService;

    /**
     * @param AiImportService $aiService
     */
    public function __construct(AiImportService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display the AI Import interface.
     */
    public function index()
    {
        $topics = Topic::orderBy('name')->select('id', 'name')->get();
        return view('admin.ai-import.index', compact('topics'));
    }

    /**
     * STEP 1: Upload PDF and process via AI.
     */
    public function uploadAndProcess(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'pdf_file' => 'nullable|mimes:pdf|max:51200', // 50MB
            'batch_id' => 'nullable|string',
            'start_page' => 'integer|min:1',
            'end_page' => 'integer|min:1',
        ]);

        try {
            $topicId = $request->topic_id;
            $userId = Auth::id();
            $startPage = $request->input('start_page', 1);
            $endPage = $request->input('end_page', 999);

            if ($request->has('batch_id') && $request->batch_id) {
                $batchId = $request->batch_id;
                $metadataPath = 'temp/ai_batch_' . $batchId . '.json';
                
                if (!Storage::exists($metadataPath)) {
                    return response()->json(['success' => false, 'message' => 'Session expired or invalid batch ID.'], 404);
                }

                $batchData = json_decode(Storage::get($metadataPath), true);
                $pdfPath = $batchData['pdf_path'];

                $questionsPath = 'temp/ai_batch_' . $batchId . '_questions.json';
                $existingQuestions = Storage::exists($questionsPath) 
                    ? (json_decode(Storage::get($questionsPath), true) ?: []) 
                    : [];
            } else {
                if (!$request->hasFile('pdf_file')) {
                    return response()->json(['success' => false, 'message' => 'PDF file is required for new import.'], 422);
                }

                $batchId = Str::random(20);
                $pdfFile = $request->file('pdf_file');
                $pdfPath = $pdfFile->storeAs('temp', 'ai_import_' . $batchId . '.pdf');

                $batchData = [
                    'topic_id' => $topicId,
                    'user_id' => $userId,
                    'pdf_path' => $pdfPath,
                    'start_time' => Carbon::now('Asia/Kolkata')->format('d-M-Y h:i:s A'),
                ];
                Storage::put('temp/ai_batch_' . $batchId . '.json', json_encode($batchData));
                $existingQuestions = [];
            }

            // AI Extraction Logic moved to Service
            $newQuestions = [];
            try {
                $newQuestions = $this->aiService->callGeminiApi(Storage::path($pdfPath), $startPage, $endPage);
            } catch (\Exception $e) {
                Log::warning("AI Processing Chunk Failed [Pages {$startPage}-{$endPage}]: " . $e->getMessage());
                // We return success true but with zero questions if chunk fails, allowing retry or next chunk
            }

            // Metadata Enrichment
            $currentIndex = count($existingQuestions);
            foreach ($newQuestions as &$nq) {
                $nq['original_index'] = $currentIndex++;
                $nq['source_page'] = $startPage;
            }

            $allQuestions = array_merge($existingQuestions, $newQuestions);
            Storage::put('temp/ai_batch_' . $batchId . '_questions.json', json_encode($allQuestions));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'questions' => $allQuestions,
                'start_time' => $batchData['start_time'] ?? '--',
            ]);
        } catch (\Exception $e) {
            Log::error('AI Import Controller Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

        $batchId = $request->batch_id;
        $qIndex = (int) $request->question_index;
        $imageType = $request->image_type ?? 'question';

        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';
        if (!Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Batch file missing.'], 404);
        }

        try {
            // Encode Base64 to public storage
            $imageParts = explode(';base64,', $request->image_base64);
            $decodedImage = base64_decode($imageParts[1]);
            $extension = str_contains($imageParts[0], 'png') ? 'png' : 'jpg';

            $dir = 'ai_extracted/' . $batchId;
            Storage::disk('public')->makeDirectory($dir);

            $fileName = $dir . '/img_' . Str::random(8) . '_' . $qIndex . '.' . $extension;
            Storage::disk('public')->put($fileName, $decodedImage);
            $imageUrl = Storage::url($fileName);

            // Thread-safe update using Cache Lock
            $lock = Cache::lock('ai_sync_' . $batchId, 10);
            $lock->block(5);

            try {
                $questions = json_decode(Storage::get($jsonFile), true);
                if (isset($questions[$qIndex])) {
                    $imgHtml = '<img src="' . $imageUrl . '" class="ai-img rounded shadow-sm max-w-full my-2" />';
                    
                    if ($imageType === 'question') {
                        if (isset($questions[$qIndex]['question'])) {
                            $questions[$qIndex]['question'] = str_replace('[IMAGE HERE]', $imgHtml, $questions[$qIndex]['question']);
                            if (!str_contains($questions[$qIndex]['question'], $imgHtml)) {
                                $questions[$qIndex]['question'] .= '<br>' . $imgHtml;
                            }
                        } else {
                            $questions[$qIndex]['question'] = $imgHtml;
                        }
                    } else {
                        $optIdx = (int) str_replace('option_', '', $imageType);
                        if (isset($questions[$qIndex]['options'][$optIdx])) {
                            $questions[$qIndex]['options'][$optIdx] = str_replace('[IMAGE HERE]', $imgHtml, $questions[$qIndex]['options'][$optIdx]);
                            if (!str_contains($questions[$qIndex]['options'][$optIdx], $imgHtml)) {
                                $questions[$qIndex]['options'][$optIdx] = $imgHtml;
                            }
                        }
                    }
                    Storage::put($jsonFile, json_encode($questions));
                }
            } finally {
                $lock->release();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Cropped Image Upload Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Preview extracted data.
     */
    public function preview($batchId)
    {
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';
        if (!Storage::exists($jsonFile)) {
            return redirect()->route('admin.ai-import.index')->with('error', 'Session expired.');
        }

        $questions = json_decode(Storage::get($jsonFile), true) ?? [];
        return view('admin.ai-import.preview', compact('questions', 'batchId'));
    }

    /**
     * Approve and persist data to DB.
     */
    public function approve(Request $request, $batchId)
    {
        $metaFile = 'temp/ai_batch_' . $batchId . '.json';
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!Storage::exists($metaFile) || !Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Required files missing.']);
        }

        try {
            $batchData = json_decode(Storage::get($metaFile), true);
            $questions = json_decode(Storage::get($jsonFile), true) ?? [];

            // Persistence handled by Service
            $count = $this->aiService->importQuestions($questions, (int)$batchData['topic_id'], (int)Auth::id());

            // Post-success Cleanup
            if (isset($batchData['pdf_path'])) {
                Storage::delete($batchData['pdf_path']);
            }
            Storage::delete([$metaFile, $jsonFile]);

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
        $batchId = $request->batch_id;
        Storage::disk('public')->deleteDirectory('ai_extracted/' . $batchId);
        Storage::delete([
            'temp/ai_batch_' . $batchId . '.json',
            'temp/ai_batch_' . $batchId . '_questions.json',
            'temp/ai_import_' . $batchId . '.pdf'
        ]);
        return response()->json(['success' => true]);
    }
}
