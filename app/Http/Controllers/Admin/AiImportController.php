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
        set_time_limit(0);

        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:20000',
            'topic_id' => 'required|exists:topics,id'
        ]);

        try {
            $topicId = $request->topic_id;
            $file = $request->file('pdf_file');

            // 1. Capture User ID then UNLOCK SESSION immediately
            $userId = Auth::id();
            session()->save(); // FIX: Page loading issue resolved

            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            $fullText = $pdf->getText();
            // Remove non-printable characters
            $fullText = preg_replace('/[^\x20-\x7E\n\t]/', '', $fullText);

            if (strlen($fullText) < 50) {
                return response()->json(['success' => false, 'message' => 'PDF empty.'], 422);
            }

            // Chunking Strategy
            $chunkSize = 3000;
            $overlap = 200;
            $textChunks = [];
            $length = strlen($fullText);

            for ($i = 0; $i < $length; $i += ($chunkSize - $overlap)) {
                $textChunks[] = substr($fullText, $i, $chunkSize);
            }

            $batchId = Str::random(20);

            // Save Batch Data to JSON
            $batchData = [
                'topic_id' => $topicId,
                'user_id' => $userId,
                'total_chunks' => count($textChunks),
                'chunks' => $textChunks,
                'start_time' => Carbon::now('Asia/Kolkata')->format('d-M-Y h:i:s A')
            ];

            Storage::put('temp/ai_batch_' . $batchId . '.json', json_encode($batchData));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'total_chunks' => count($textChunks),
                'start_time' => $batchData['start_time']
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * STEP 2: Process a single chunk via AI (AJAX).
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

        $batchId = $request->batch_id;
        $index = $request->chunk_index;
        $fileName = 'temp/ai_batch_' . $batchId . '.json';

        if (!Storage::exists($fileName)) {
            // Returns 404 to trigger client-side stop
            return response()->json(['success' => false, 'message' => 'STOPPED'], 404);
        }

        $batchData = json_decode(Storage::get($fileName), true);

        if (!isset($batchData['chunks'][$index])) {
            return response()->json(['success' => false, 'message' => 'Chunk not found.']);
        }

        $textSegment = $batchData['chunks'][$index];
        $topicId = $batchData['topic_id'];
        $creatorId = $batchData['user_id'] ?? 1;

        try {
            // 2. TIMEOUT FIX: Increased to 240 seconds

            /** * @var \Illuminate\Http\Client\Response $response
             * Explicit type hinting to fix Intelephense/IDE "undefined method status()" errors.
             */
            $response = Http::timeout(240)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => url('/'),
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => 'deepseek/deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an SME. Extract questions, SOLVE them (find correct answer), output JSON.'],
                        ['role' => 'user', 'content' => $this->getPrompt($textSegment)]
                    ],
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
            $aiContent = $jsonResponse['choices'][0]['message']['content'] ?? null;

            if (!$aiContent) throw new \Exception("Empty Response from AI");

            // Clean Markdown code blocks if present
            $cleanedJson = preg_replace('/```json|```/', '', $aiContent);
            $questionsArray = json_decode($cleanedJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                 return response()->json(['success' => true, 'processed_count' => 0, 'warning' => 'Invalid JSON skipped']);
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
     * @param string $text The text segment to analyze
     * @return string
     */
    private function getPrompt($text) {
        return <<<EOT
        Analyze text. Extract MSA Questions. Solve them.
        Rules: Return ONLY raw JSON array.
        Format: [{"question":"...","options":["A","B","C","D"],"correct_option_index":0,"solution":"..."}]
        TEXT: $text
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

                Question::create([
                    'question_type_id' => $defaultType->id,
                    'skill_id' => $skill->id,
                    'topic_id' => $topic->id,
                    'difficulty_level_id' => $defaultDiff->id,
                    'question' => $qData['question'],
                    'options' => $formattedOptions,
                    'correct_answer' => (int)$qData['correct_option_index'] + 1,
                    'solution' => $qData['solution'] ?? '',
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
                continue;
            }
        }
        return $insertedCount;
    }
}
