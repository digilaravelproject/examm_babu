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

            $count = $this->insertQuestionsToDB($questionsArray, $topicId, $creatorId);

            return response()->json([
                'success' => true,
                'processed_count' => $count
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
        2. Extract the Question Stem and Options (A, B, C, D).
        3. ACCURACY IS CRITICAL. If options are jumbled, use logic to separate them. Ignore unrelated text like page numbers or headers.
        4. FIND THE CORRECT ANSWER.
           - If the answer key is present in the text, use it.
           - If NO answer key is found, you MUST SOLVE the question yourself and provide the correct option index (0-3).
        5. GENERATE A HINT/EXPLANATION.
           - Provide a short "hint" or "solution" explanation for the student.

        OUTPUT FORMAT:
        Return ONLY a raw JSON array of objects. Do not output markdown code blocks (```json).

        JSON STRUCTURE:
        [
            {
                "question": "The question text here...",
                "options": ["Option A", "Option B", "Option C", "Option D"],
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
}
