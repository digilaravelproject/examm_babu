<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
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
 *
 * @package App\Http\Controllers\Admin
 */
class AiImportController extends Controller
{
    /**
     * @var QuestionRepository
     */
    protected $repository;

    /**
     * AiImportController constructor.
     *
     * @param QuestionRepository $repository
     */
    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display the AI Import interface.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $topics = Topic::orderBy('name')->select('id', 'name')->get();
        return view('admin.ai-import.index', compact('topics'));
    }

    /**
     * STEP 1: Upload PDF, parse text, and prepare chunks for processing.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAndPrepare(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'total_chunks' => 'required|integer|min:1'
        ]);

        try {
            $topicId = $request->topic_id;

            // 1. Capture User ID then UNLOCK SESSION immediately
            $userId = Auth::id();
            session()->save(); // FIX: Page loading issue resolved

            $totalChunks = (int) $request->total_chunks;
            $batchId = Str::random(20);

            // Save Batch Data to JSON
            $batchData = [
                'topic_id' => $topicId,
                'user_id' => $userId,
                'total_chunks' => $totalChunks,
                'start_time' => Carbon::now('Asia/Kolkata')->format('d-M-Y h:i:s A')
            ];

            Storage::put('temp/ai_batch_' . $batchId . '.json', json_encode($batchData));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'total_chunks' => $totalChunks,
                'start_time' => $batchData['start_time']
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * STEP 2: Process a single chunk via AI (AJAX).
     * Now accepts an image_base64 parameter containing the rendered PDF page.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processChunk(Request $request)
    {
        // 1. PERFORMANCE: Close session immediately so other tabs work
        if (session()->isStarted()) {
            session()->save();
        }

        set_time_limit(0);

        $request->validate([
            'batch_id' => 'required|string',
            'chunk_index' => 'required|integer',
            'image_base64' => 'required|string'
        ]);

        $batchId = $request->batch_id;
        $index = $request->chunk_index;
        $imageBase64 = $request->image_base64; // e.g., data:image/jpeg;base64,...

        $fileName = 'temp/ai_batch_' . $batchId . '.json';

        if (!Storage::exists($fileName)) {
            // Returns 404 to trigger client-side stop
            return response()->json(['success' => false, 'message' => 'STOPPED'], 404);
        }

        $batchData = json_decode(Storage::get($fileName), true);

        $topicId = $batchData['topic_id'];
        $creatorId = $batchData['user_id'] ?? 1;

        try {
            // 2. TIMEOUT FIX: Increased to 240 seconds
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(240)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => url('/'),
                    'X-Title' => env('APP_NAME', 'Exam Babu'),
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => 'google/gemini-2.5-flash',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $this->getPrompt()],
                                ['type' => 'image_url', 'image_url' => ['url' => $imageBase64]]
                            ]
                        ]
                    ],
                    'max_tokens' => 3000,
                    'temperature' => 0.1
                ]);

            if ($response->failed()) {
                // Rate Limit Handling
                if ($response->status() == 429) {
                    throw new \Exception("Rate Limit Exceeded. Please stop and wait.");
                }
                // Server Error
                if ($response->serverError()) {
                    throw new \Exception("AI Server Error (5xx). Trying next chunk...");
                }
            }

            $jsonResponse = $response->json();

            // DEBUG: Log the raw response to understand why it's empty
            if (!isset($jsonResponse['choices'][0]['message']['content'])) {
                 Log::error('AI Import Error - Raw Response:', ['body' => $response->body(), 'status' => $response->status()]);
            }

            $aiContent = $jsonResponse['choices'][0]['message']['content'] ?? null;

            if (!$aiContent) throw new \Exception("Empty Response from AI - Check Logs");

            // Robust JSON Extraction
            $firstBracket = strpos($aiContent, '[');
            $lastBracket = strrpos($aiContent, ']');

            if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
                $cleanedJson = substr($aiContent, $firstBracket, $lastBracket - $firstBracket + 1);
            } else {
                // Fallback: Try to clean markdown code blocks
                 $cleanedJson = preg_replace('/```json|```/', '', $aiContent);
            }

            $questionsArray = json_decode($cleanedJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                 Log::error("AI Import JSON Error: " . json_last_error_msg() . " Content: " . substr($cleanedJson, 0, 500));
                 return response()->json(['success' => true, 'processed_count' => 0, 'warning' => 'Invalid JSON Structure from AI.']);
            }

            // Image clipping if bounding box exists
            $imgBase64Raw = preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64);
            $imgData = base64_decode($imgBase64Raw);
            $imgResource = @imagecreatefromstring($imgData);

            if ($imgResource !== false) {
                $imgWidth = imagesx($imgResource);
                $imgHeight = imagesy($imgResource);

                foreach ($questionsArray as &$q) {
                    if (isset($q['image_box']) && is_array($q['image_box']) && count($q['image_box']) === 4) {
                        try {
                            $box = $q['image_box'];
                            $ymin = max(0, min(1000, $box[0])) / 1000 * $imgHeight;
                            $xmin = max(0, min(1000, $box[1])) / 1000 * $imgWidth;
                            $ymax = max(0, min(1000, $box[2])) / 1000 * $imgHeight;
                            $xmax = max(0, min(1000, $box[3])) / 1000 * $imgWidth;

                            $cW = $xmax - $xmin;
                            $cH = $ymax - $ymin;

                            if ($cW > 0 && $cH > 0) {
                                $cropped = imagecrop($imgResource, ['x' => $xmin, 'y' => $ymin, 'width' => $cW, 'height' => $cH]);
                                if ($cropped !== false) {
                                    ob_start();
                                    imagejpeg($cropped, null, 85);
                                    $cBase64 = base64_encode(ob_get_clean());
                                    $q['cropped_image_base64'] = 'data:image/jpeg;base64,' . $cBase64;
                                    imagedestroy($cropped);

                                    // Append image to question text
                                    $q['question'] .= '<br><img src="' . $q['cropped_image_base64'] . '" class="my-2 border rounded shadow-sm max-w-full" alt="Extracted Image" />';
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to crop image for question", ['err' => $e->getMessage()]);
                        }
                    }
                }
                imagedestroy($imgResource);
            }

            // Save to temp JSON instead of inserting immediately
            $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';
            $existing = [];
            if (Storage::exists($jsonFile)) {
                $existing = json_decode(Storage::get($jsonFile), true) ?? [];
            }
            $existing = array_merge($existing, $questionsArray);
            Storage::put($jsonFile, json_encode($existing));

            return response()->json([
                'success' => true,
                'processed_count' => count($questionsArray)
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle Timeout specifically
            return response()->json(['success' => false, 'message' => 'Timeout: AI took too long.'], 504);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * STEP 3: Cancel or Stop the import process.
     * Deletes the temporary batch file.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelImport(Request $request)
    {
        $batchId = $request->batch_id;
        $fileName = 'temp/ai_batch_' . $batchId . '.json';

        if (Storage::exists($fileName)) {
            Storage::delete($fileName); // Delete file so next chunks fail gracefully
        }

        return response()->json(['success' => true]);
    }

    /**
     * Generates the system prompt for the AI.
     *
     * @return string
     */
    private function getPrompt() {
        return <<<EOT
        You are an expert academic assistant used to digitize exam questions from PDF pages.

        YOUR TASK:
        1. Analyze the provided image of a question paper and extract all multiple-choice questions (MSA).
        2. KEEP QUESTIONS IN THE EXACT SAME ORDER as they appear in the original document.
        3. Extract the Question Stem and Options (A, B, C, D).
        4. If a question contains a diagram, graph, equation, or picture as part of the question itself, output its bounding box as a 4-element array [ymin, xmin, ymax, xmax] normalized to 0-1000 scale in the `image_box` field. E.g., `[250, 100, 450, 900]`. If there's no image, set `image_box` to `null`.
        5. ACCURACY IS CRITICAL. If options are jumbled, use logic to separate them. Ignore unrelated text like page numbers or headers.
        6. FIND THE CORRECT ANSWER.
           - If the answer key is present in the text, use it.
           - If NO answer key is found, you MUST SOLVE the question yourself and provide the correct option index (0-3).
        7. GENERATE A HINT/EXPLANATION.
           - Provide a short "hint" or "solution" explanation for the student.

        OUTPUT FORMAT:
        Return ONLY a raw JSON array of objects. Do not output markdown code blocks (```json).

        JSON STRUCTURE:
        [
            {
                "question": "The question text here...",
                "options": ["Option A", "Option B", "Option C", "Option D"],
                "image_box": [100, 200, 300, 400],
                "correct_option_index": 0,  // Integer: 0 for A, 1 for B, 2 for C, 3 for D
                "solution": "Detailed explanation of why this option is correct.",
                "hint": "A short clue to help the student."
            }
        ]
        EOT;
    }

    /**
     * Inserts the parsed questions into the database.
     *
     * @param array $questionsData Array of questions from AI
     * @param int $topicId
     * @param int $userId
     * @return int Number of inserted questions
     */
    private function insertQuestionsToDB($questionsData, $topicId, $userId) {
        $insertedCount = 0;
        $topic = Topic::find($topicId);
        $skill = $topic ? $topic->skill : Skill::first();
        $defaultType = QuestionType::where('code', 'MSA')->first();
        $defaultDiff = DifficultyLevel::where('code', 'EASY')->first();

        // Row number simulator for uniqueness
        $rowNum = rand(100, 999);

        foreach ($questionsData as $qData) {
            // Basic Validation
            if (empty($qData['question']) || empty($qData['options']) || !is_array($qData['options'])) {
                continue;
            }

            // Duplicate Check
            $exists = Question::where('topic_id', $topic->id)
                ->where('question', 'LIKE', substr($qData['question'], 0, 100).'%')
                ->exists();

            if ($exists) continue;

            DB::beginTransaction();
            try {
                $formattedOptions = [];
                foreach ($qData['options'] as $optText) {
                    $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                }

                // Generate Code: que_ai_{TIMESTAMP}_{SKILL_ID}_{ROW_NUM}
                $code = 'que_ai_' . now()->setTimezone('Asia/Kolkata')->format('Ymd_His') . '_' . $skill->id . '_' . $rowNum;

                // Determine Correct Answer (1-based index for DB)
                $correctIndex = isset($qData['correct_option_index']) ? (int)$qData['correct_option_index'] : 0;
                $correctAnswer = $correctIndex + 1; // Convert 0-based to 1-based

                Question::create([
                    'question_type_id' => $defaultType->id,
                    'skill_id' => $skill->id,
                    'topic_id' => $topic->id,
                    'difficulty_level_id' => $defaultDiff->id,
                    'question' => $qData['question'],
                    'options' => $formattedOptions,
                    'correct_answer' => $correctAnswer,
                    'solution' => $qData['solution'] ?? '',
                    'hint' => $qData['hint'] ?? '', // Added Hint mapping
                    'default_marks' => 1,
                    'default_time' => 60,
                    'preferences' => $this->repository->setDefaultPreferences('MSA'),
                    'created_by' => $userId,
                    'is_active' => 1,
                    'code' => $code
                ]);

                DB::commit();
                $insertedCount++;
                $rowNum++;
            } catch (\Exception $e) {
                DB::rollBack();
                // Log error if needed, but continue processing matches
                continue;
            }
        }
        return $insertedCount;
    }

    /**
     * Preview the imported questions before saving them.
     */
    public function preview($batchId)
    {
        $metaFile = 'temp/ai_batch_' . $batchId . '.json';
        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';

        if (!Storage::exists($metaFile) || !Storage::exists($jsonFile)) {
            return redirect()->route('admin.ai-import.index')->with('error', 'Batch not found or expired.');
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
            return response()->json(['success' => false, 'message' => 'Batch not found.']);
        }

        $batchData = json_decode(Storage::get($metaFile), true);
        $questions = json_decode(Storage::get($jsonFile), true) ?? [];

        $topicId = $batchData['topic_id'];
        $creatorId = $batchData['user_id'] ?? 1;

        $count = $this->insertQuestionsToDB($questions, $topicId, $creatorId);

        // Delete temp files
        Storage::delete([$metaFile, $jsonFile]);

        $request->session()->flash('success', "Successfully imported {$count} questions!");

        return response()->json(['success' => true, 'redirect' => route('admin.ai-import.index')]);
    }
}
