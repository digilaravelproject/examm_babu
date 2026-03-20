<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Topic;
use App\Models\Skill;
use App\Models\DifficultyLevel;
use App\Repositories\QuestionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class AiImportController
 *
 * Handles the extraction of questions from PDF files using AI.
 * Steps: Upload PDF -> Parse Text -> Chunk Text -> Process via AI -> Insert to DB.
 */
class AiImportController extends Controller
{
    protected $repository;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
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
     * STEP 1: Upload PDF and process via Gemini AI.
     */
    public function uploadAndProcess(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
        ]);

        if (!$request->has('batch_id')) {
            $request->validate([
                'pdf_file' => 'required|mimes:pdf|max:102400' // 100MB
            ]);
        }

        try {
            $topicId = $request->topic_id;
            $userId = Auth::id();
            
            // Chunking parameters (default to whole document if not provided)
            $startPage = $request->input('start_page', 1);
            $endPage = $request->input('end_page', 999);

            if ($request->has('batch_id')) {
                $batchId = $request->batch_id;
                $batchData = json_decode(Storage::get('temp/ai_batch_' . $batchId . '.json'), true);
                $pdfPath = $batchData['pdf_path'];
                
                // Read existing questions if any
                $existingQuestions = [];
                if (Storage::exists('temp/ai_batch_' . $batchId . '_questions.json')) {
                    $existingQuestions = json_decode(Storage::get('temp/ai_batch_' . $batchId . '_questions.json'), true) ?: [];
                }
            } else {
                $batchId = Str::random(20);
                
                // Store PDF temporarily
                $pdfFile = $request->file('pdf_file');
                $pdfPath = $pdfFile->storeAs('temp', 'ai_import_' . $batchId . '.pdf');

                // Save batch metadata
                $batchData = [
                    'topic_id' => $topicId,
                    'user_id' => $userId,
                    'pdf_path' => $pdfPath,
                    'start_time' => Carbon::now('Asia/Kolkata')->format('d-M-Y h:i:s A')
                ];
                Storage::put('temp/ai_batch_' . $batchId . '.json', json_encode($batchData));
                $existingQuestions = [];
            }

            // Call Gemini API (with chunking instructions)
            // Even if it fails to find questions in this chunk, we suppress the error so the process continues
            $newQuestions = [];
            try {
                $newQuestions = $this->callGeminiApi(Storage::path($pdfPath), $startPage, $endPage);
            } catch (\Exception $e) {
                Log::warning("Gemini skipped chunk pages {$startPage}-{$endPage}: " . $e->getMessage());
            }

            // Append new questions to existing ones
            $allQuestions = array_merge($existingQuestions, $newQuestions);

            // Save extracted questions to temporary JSON
            Storage::put('temp/ai_batch_' . $batchId . '_questions.json', json_encode($allQuestions));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'questions' => $allQuestions,
                'start_time' => $batchData['start_time']
            ]);

        } catch (\Exception $e) {
            Log::error("Gemini Import Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Call Gemini API to extract questions.
     */
    private function callGeminiApi($pdfPath, $startPage = 1, $endPage = 999)
    {
        // Increase PHP execution time limit to prevent premature termination during long AI generation
        set_time_limit(1200);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            throw new \Exception("GEMINI_API_KEY not found in the .env file.");
        }

        if (!file_exists($pdfPath)) {
            Log::error("PDF File missing at path: " . $pdfPath);
            throw new \Exception("System error: Uploaded PDF file not found.");
        }

        $pdfBase64 = base64_encode(file_get_contents($pdfPath));
        
        /** @var \Illuminate\Http\Client\Response $response */
        // Increase Laravel and cURL timeout limits to handle large 50-100 question extractions
        $response = Http::timeout(1200)->withOptions([
            \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 60,
            \GuzzleHttp\RequestOptions::TIMEOUT => 1200,
            'verify' => false // Prevent local SSL peer verification issues on some XAMPP setups
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $this->getGeminiPrompt($startPage, $endPage)],
                        [
                            'inline_data' => [
                                'mime_type' => 'application/pdf',
                                'data' => $pdfBase64
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.0,
                'maxOutputTokens' => 8192, 
            ]
        ]);

        if (!$response->successful()) {
            $errorBody = $response->body() ?: "Unknown error";
            Log::error("Gemini API HTTP Error: " . $errorBody);
            throw new \Exception("Gemini API Error: The model failed to provide a valid response. Check laravel.log.");
        }

        $result = $response->json();

        if (isset($result['promptFeedback']['blockReason'])) {
            Log::error("Gemini Blocked Request: " . json_encode($result['promptFeedback']));
            throw new \Exception("AI blocked the request due to policy/safety limits. Please check the PDF content.");
        }

        $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$content) {
            Log::error("Gemini Empty Content. Full API Response: " . json_encode($result));
            throw new \Exception("Received an empty response from Gemini. Check laravel.log.");
        }

        // CRITICAL FIX: Strip markdown formatting (```json) before decoding
        $cleanContent = preg_replace('/^```json\s*/i', '', trim($content));
        $cleanContent = preg_replace('/```$/', '', trim($cleanContent));
        
        // NEW: Sanitize hidden control characters that cause "Control character error"
        $cleanContent = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $cleanContent);
        $cleanContent = trim($cleanContent);

        $decoded = json_decode($cleanContent, true);

        // AUTO-REPAIR: If JSON is truncated (common with large PDFs)
        if (json_last_error() !== JSON_ERROR_NONE) {
            $repairedContent = trim($cleanContent);
            
            // If it looks like an array that didn't close
            if (strpos($repairedContent, '[') === 0 && substr($repairedContent, -1) !== ']') {
                // Remove trailing comma if exists
                if (substr($repairedContent, -1) === ',') {
                    $repairedContent = substr($repairedContent, 0, -1);
                }
                
                // Try closing the objects and array
                // We add multiple closing braces just in case it cut off inside an object
                $tempRepaired = $repairedContent . '}]'; 
                $testDecoded = json_decode($tempRepaired, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    $decoded = $testDecoded;
                    Log::info("Gemini JSON Auto-Repaired: Fixed truncated array structure.");
                } else {
                    // Try one more level if it was deeper (extremely rare)
                    $tempRepaired = $repairedContent . ']';
                    $testDecoded = json_decode($tempRepaired, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $decoded = $testDecoded;
                        Log::info("Gemini JSON Auto-Repaired: Fixed truncated array structure (level 2).");
                    }
                }
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON Decode Failed. Error: " . json_last_error_msg() . " | Raw Content: " . $cleanContent);
            throw new \Exception("AI response was too long and got cut off. I tried to repair it but failed. Please try a smaller PDF or check the document.");
        }

        return $decoded;
    }

    /**
     * Handle uploaded cropped images from the frontend.
     * FIXED: Saving as file instead of storing huge Base64 string.
     */
    public function uploadCroppedImage(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'question_index' => 'required|integer',
            'image_base64' => 'required|string',
            'image_type' => 'nullable|string' // 'question' or 'option_0', 'option_1', etc.
        ]);

        $batchId = $request->batch_id;
        $qIndex = $request->question_index;
        $imageBase64 = $request->image_base64;
        $imageType = $request->image_type ?? 'question';

        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';
        if (!Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        }

        // Decode Base64 and convert to .jpg file
        $imageParts = explode(";base64,", $imageBase64);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'jpg';
        $imageBase64Decoded = base64_decode($imageParts[1]);

        // FIXED: Use batch-specific directory for cleaner cleanup
        $dir = 'ai_extracted/' . $batchId;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $fileName = $dir . '/img_' . uniqid() . '_' . $qIndex . '.' . $imageType;
        Storage::disk('public')->put($fileName, $imageBase64Decoded);

        // Generate public URL
        $imageUrl = Storage::url($fileName);

        /**
         * RACE CONDITION FIX: Use Cache::lock to prevent data loss when parallel uploads
         * try to write to the same questions.json simultaneously.
         */
        $lock = Cache::lock('ai_import_batch_' . $batchId, 15);

        try {
            $lock->block(10); // Wait up to 10 seconds to acquire lock

            $questions = json_decode(Storage::get($jsonFile), true);
            if (isset($questions[$qIndex])) {
                $imgTag = '<img src="' . $imageUrl . '" class="my-2 border rounded shadow-sm max-w-full" alt="Extracted Image" />';

                if ($imageType === 'question') {
                    if (strpos($questions[$qIndex]['question'], '[IMAGE HERE]') !== false) {
                        $questions[$qIndex]['question'] = str_replace('[IMAGE HERE]', $imgTag, $questions[$qIndex]['question']);
                    } else {
                        $questions[$qIndex]['question'] .= '<br>' . $imgTag;
                    }
                } elseif (strpos($imageType, 'option_') === 0) {
                    $optIndex = (int) str_replace('option_', '', $imageType);
                    if (isset($questions[$qIndex]['options'][$optIndex])) {
                        if (strpos($questions[$qIndex]['options'][$optIndex], '[IMAGE HERE]') !== false) {
                            $questions[$qIndex]['options'][$optIndex] = str_replace('[IMAGE HERE]', $imgTag, $questions[$qIndex]['options'][$optIndex]);
                        } else {
                            $questions[$qIndex]['options'][$optIndex] = $imgTag;
                        }
                    }
                }

                Storage::put($jsonFile, json_encode($questions));
            }

        } catch (\Exception $e) {
            Log::error("Race Condition Error in AiImport: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Image save karne me error: ' . $e->getMessage()], 500);
        } finally {
            $lock->release();
        }

        return response()->json(['success' => true]);
    }

    /**
     * STEP 3: Cancel or Stop the import process.
     */
    public function cancelImport(Request $request)
    {
        $batchId = $request->batch_id;
        $metaFile = 'temp/ai_batch_' . $batchId . '.json';
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (Storage::exists($metaFile)) {
            $batchData = json_decode(Storage::get($metaFile), true);
            if (isset($batchData['pdf_path'])) {
                Storage::delete($batchData['pdf_path']);
            }
            // FIXED: Delete entire batch folder (Simple & Clean!)
            Storage::disk('public')->deleteDirectory('ai_extracted/' . $batchId);
            Storage::delete($metaFile);
        }
        if (Storage::exists($jsonFile)) {
            Storage::delete($jsonFile);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Updated Gemini Prompt.
     */
    private function getGeminiPrompt($startPage, $endPage)
    {
        return <<<EOT
        You are a highly precise academic digitization system. Your goal is to extract ALL questions (aiming for up to 100 questions if the document contains them) from the provided PDF document with 100% accuracy.

        SUPPORTED QUESTION TYPES:
        - `MSA`: Multiple Choice Single Answer (Exactly one correct option).
        - `MMA`: Multiple Choice Multiple Answer (One or more correct options).
        - `TOF`: True/False (Exactly two options: True and False).
        - `FIB`: Fill in the Blanks (No options, just the question text with blanks like ___).
        - `SAQ`: Short Answer Question (No options, requires a brief text response).

        STRICT RULES:
        1. MASS EXTRACTION (CHUNKING): You MUST ONLY extract questions that appear on pages {$startPage} through {$endPage} (inclusive). IGNORE any questions outside of this range. Extract AS MANY QUESTIONS AS POSSIBLE from this specific page range.
        2. SEQUENTIAL EXTRACTION: Extract questions in the EXACT ORDER they appear in the specified pages. Do NOT skip any questions.
        3. TYPE DETECTION: Categorize each question into one of the types above based on its structure in the PDF.
        3. CONTENT PRECISION:
           - Extract the full question text.
           - For `MSA` and `MMA`: Extract 4 options (A, B, C, D).
           - If an option is an image or contains a mathematical diagram, set its text to `[IMAGE HERE]` and provide its bounding box `[ymin, xmin, ymax, xmax]` in the `option_image_boxes` object, using the option's index (0-3) as the key.
           - For `TOF`: The options must be exactly ["True", "False"].
           - For `FIB` and `SAQ`: Set `options` to an empty array.
        4. CORRECT ANSWER (MANDATORY):
           - FOR EVERY QUESTION, you MUST identify the correct answer.
           - Look for cues like "(Ans)", "Answer:", "Correct Option:", or distinct bolding.
           - For `MSA` and `TOF`: `correct_option_index` must be a valid integer (0-3).
           - For `MMA`: `correct_option_indices` is an array of integers (e.g., [0, 2]).
           - For `FIB` and `SAQ`: `correct_answer_text` is the expected string answer.
           - If no answer is explicitly written, use your reasoning to determine the scientifically correct one. Do NOT leave it null.
        5. IMAGE/DIAGRAM DETECTION:
           - For EVERY question, check if the main question has a related diagram, table, or figure.
           - If a visual is present: Provide the 1-indexed `page_number` and a precise `image_box` [ymin, xmin, ymax, xmax] (normalized 0-1000).
           - If options have images, provide them in `option_image_boxes` as specified in rule 3.
           - Accurate bounding boxes are CRITICAL for our system to crop the images.
        6. LANGUAGE: Preserve scripts (Hindi, Marathi, etc.) exactly.
        7. TOKEN EFFICIENCY & PRIORITY:
           - Your FIRST priority is extracting the Question, Options, and all Image Bounding Boxes.
           - ONLY provide a `solution` or `hint` if it is EXPLICITLY written in the PDF content.
           - If no solution/hint is clearly provided in the PDF, leave them as empty strings (""). Do NOT generate them yourself.
           - STRICT LIMIT: If providing them, they MUST be exactly ONE line each (maximum 10-15 words).
           - Do NOT use long paragraphs. If you are running out of space, stop generating text and close the JSON array cleanly.

        IMPORTANT: If you run close to your token limit, ensure you at least close the JSON structures correctly.
        OUTPUT FORMAT: A JSON array of objects.

        JSON SCHEMA:
        {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "type": {"type": "string", "enum": ["MSA", "MMA", "TOF", "FIB", "SAQ"]},
              "question": {"type": "string"},
              "options": {"type": "array", "items": {"type": "string"}},
              "image_box": {"type": "array", "items": {"type": "number"}, "minItems": 4, "maxItems": 4},
              "option_image_boxes": {
                "type": "object",
                "additionalProperties": {
                  "type": "array",
                  "items": {"type": "number"},
                  "minItems": 4,
                  "maxItems": 4
                }
              },
              "page_number": {"type": "integer"},
              "correct_option_index": {"type": "integer", "minimum": 0, "maximum": 3},
              "correct_option_indices": {"type": "array", "items": {"type": "integer"}},
              "correct_answer_text": {"type": "string"},
              "solution": {"type": "string"},
              "hint": {"type": "string"}
            },
            "required": ["type", "question", "options", "solution", "hint"]
          }
        }
        EOT;
    }

    /**
     * Inserts the parsed questions into the database.
     * FIXED N+1 Issue and Transaction bottleneck.
     */
    private function insertQuestionsToDB($questionsData, $topicId, $userId)
    {
        $insertedCount = 0;
        $topic = Topic::with('skill')->find($topicId);
        if (!$topic)
            return 0;

        $skill = $topic->skill ?? Skill::first();
        $defaultDiff = DifficultyLevel::where('code', 'EASY')->first();

        // Cache question types
        $qTypes = QuestionType::all()->keyBy('code');

        // FIXED N+1 Issue: Fetch existing questions for this topic ONCE before the loop
        $existingQuestionsRaw = Question::where('topic_id', $topic->id)->pluck('question')->toArray();
        $existingPrefixes = [];
        foreach ($existingQuestionsRaw as $rawQ) {
            // Remove HTML tags and use first 100 characters for matching (Professional trimmed matching)
            $existingPrefixes[] = strtolower(trim(substr(strip_tags($rawQ), 0, 100)));
        }

        // FIXED Transaction: Start transaction outside the loop
        DB::beginTransaction();
        try {
            foreach ($questionsData as $qData) {
                if (empty($qData['question']))
                    continue;

                // Perform string comparison to avoid N+1 queries
                $cleanQuestionPrefix = strtolower(trim(substr(strip_tags($qData['question']), 0, 100)));
                $exists = false;
                foreach ($existingPrefixes as $prefix) {
                    if ($prefix !== '' && strpos($cleanQuestionPrefix, $prefix) === 0) {
                        $exists = true;
                        break;
                    }
                }

                if ($exists)
                    continue; // Skip duplicates

                $typeCode = $qData['type'] ?? 'MSA';
                $type = $qTypes->get($typeCode) ?: $qTypes->get('MSA');

                $formattedOptions = [];
                $correctAnswer = null;

                switch ($typeCode) {
                    case 'MMA':
                        foreach ($qData['options'] as $optText) {
                            $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                        }
                        $indices = is_array($qData['correct_option_indices']) ? $qData['correct_option_indices'] : [];
                        $correctAnswer = array_map(fn($i) => (int) $i + 1, $indices);
                        break;
                    case 'TOF':
                    case 'MSA':
                        foreach ($qData['options'] as $optText) {
                            $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                        }
                        $correctAnswer = (isset($qData['correct_option_index']) ? (int) $qData['correct_option_index'] : 0) + 1;
                        break;
                    case 'FIB':
                    case 'SAQ':
                        $formattedOptions = [];
                        $correctAnswer = $qData['correct_answer_text'] ?? '';
                        break;
                    default:
                        $correctAnswer = $qData['correct_answer_text'] ?? '';
                }

                $code = 'que_ai_' . now()->setTimezone('Asia/Kolkata')->format('Ymd_His') . '_' . $skill->id . '_' . rand(100, 999);

                Question::create([
                    'question_type_id' => $type->id,
                    'skill_id' => $skill->id,
                    'topic_id' => $topic->id,
                    'difficulty_level_id' => $defaultDiff->id,
                    'question' => $qData['question'],
                    'options' => $formattedOptions,
                    'correct_answer' => $correctAnswer,
                    'solution' => $qData['solution'] ?? '',
                    'hint' => $qData['hint'] ?? '',
                    'default_marks' => 1,
                    'default_time' => 60,
                    'preferences' => $this->repository->setDefaultPreferences($typeCode),
                    'created_by' => $userId,
                    'is_active' => 1,
                    'code' => $code
                ]);

                // Update existing prefixes to prevent duplicates within the same batch
                $existingPrefixes[] = $cleanQuestionPrefix;
                $insertedCount++;
            }
            // Commit all questions to DB after the loop
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to insert questions batch: " . $e->getMessage());
            throw $e;
        }

        return $insertedCount;
    }

    /**
     * Preview the imported questions.
     */
    public function preview($batchId)
    {
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!Storage::exists($jsonFile)) {
            return redirect()->route('admin.ai-import.index')->with('error', 'Batch not found or session expired.');
        }

        $questions = json_decode(Storage::get($jsonFile), true) ?? [];
        return view('admin.ai-import.preview', compact('questions', 'batchId'));
    }

    /**
     * Approve and save the imported questions.
     */
    public function approve(Request $request, $batchId)
    {
        $metaFile = 'temp/ai_batch_' . $batchId . '.json';
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!Storage::exists($metaFile) || !Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Batch nahi mila.']);
        }

        $batchData = json_decode(Storage::get($metaFile), true);
        $questions = json_decode(Storage::get($jsonFile), true) ?? [];

        try {
            $count = $this->insertQuestionsToDB($questions, $batchData['topic_id'], $batchData['user_id'] ?? 1);

            // Cleanup files
            if (isset($batchData['pdf_path'])) {
                Storage::delete($batchData['pdf_path']);
            }
            // Cleanup batch images directory
            Storage::disk('public')->deleteDirectory('ai_extracted/' . $batchId);
            Storage::delete([$metaFile, $jsonFile]);

            $request->session()->flash('success', "Successfully imported {$count} questions!");
            return response()->json(['success' => true, 'redirect' => route('admin.ai-import.index')]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}
