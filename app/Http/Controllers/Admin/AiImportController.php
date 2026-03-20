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
     * STEP 1: Upload PDF and process via Gemini AI.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAndProcess(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'pdf_file' => 'required|mimes:pdf|max:102400' // 100MB
        ]);

        try {
            $topicId = $request->topic_id;
            $userId = Auth::id();
            $batchId = Str::random(20);

            // Save PDF temporarily
            $pdfFile = $request->file('pdf_file');
            $pdfPath = $pdfFile->storeAs('temp', 'ai_import_' . $batchId . '.pdf');

            // Save Batch Data
            $batchData = [
                'topic_id' => $topicId,
                'user_id' => $userId,
                'pdf_path' => $pdfPath,
                'start_time' => Carbon::now('Asia/Kolkata')->format('d-M-Y h:i:s A')
            ];
            Storage::put('temp/ai_batch_' . $batchId . '.json', json_encode($batchData));

            // Call Gemini API
            $questions = $this->callGeminiApi(storage_path('app/' . $pdfPath));

            if (empty($questions)) {
                throw new \Exception("AI could not extract any questions. Please check the PDF quality.");
            }

            // Save extracted questions to temp JSON
            Storage::put('temp/ai_batch_' . $batchId . '_questions.json', json_encode($questions));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'questions' => $questions,
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
    private function callGeminiApi($pdfPath)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) throw new \Exception("GEMINI_API_KEY not found in .env");

        $pdfBase64 = base64_encode(file_get_contents($pdfPath));

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(600)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $this->getGeminiPrompt()],
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
                'temperature' => 0.0, // Set to 0 for maximum deterministic accuracy
            ]
        ]);

        if (!$response->successful()) {
            $errorBody = $response->body() ?: "Unknown error";
            Log::error("Gemini Pro API Error: " . $errorBody);
            throw new \Exception("Gemini Pro API Error: " . $errorBody);
        }

        $result = $response->json();
        $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$content) throw new \Exception("Empty response from Gemini.");

        return json_decode($content, true);
    }

    /**
     * Handle uploaded cropped images from the frontend.
     */
    public function uploadCroppedImage(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'question_index' => 'required|integer',
            'image_base64' => 'required|string'
        ]);

        $batchId = $request->batch_id;
        $qIndex = $request->question_index;
        $imageBase64 = $request->image_base64;

        $jsonFile = 'temp/ai_batch_' . $batchId . '_questions.json';
        if (!Storage::exists($jsonFile)) {
            return response()->json(['success' => false, 'message' => 'Batch not found.'], 404);
        }

        $questions = json_decode(Storage::get($jsonFile), true);
        if (isset($questions[$qIndex])) {
            // Prepend image to question text (matching original logic)
            $questions[$qIndex]['question'] .= '<br><img src="' . $imageBase64 . '" class="my-2 border rounded shadow-sm max-w-full" alt="Extracted Image" />';
            Storage::put($jsonFile, json_encode($questions));
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
    private function getGeminiPrompt() {
        return <<<EOT
        You are a highly precise academic digitization system. Your goal is to extract questions from the provided PDF document with 100% accuracy.

        SUPPORTED QUESTION TYPES:
        - `MSA`: Multiple Choice Single Answer (Exactly one correct option).
        - `MMA`: Multiple Choice Multiple Answer (One or more correct options).
        - `TOF`: True/False (Exactly two options: True and False).
        - `FIB`: Fill in the Blanks (No options, just the question text with blanks like ___).
        - `SAQ`: Short Answer Question (No options, requires a brief text response).

        STRICT RULES:
        1. SEQUENTIAL EXTRACTION: Extract questions in the EXACT ORDER they appear in the PDF. Do NOT skip any questions.
        2. TYPE DETECTION: Categorize each question into one of the types above based on its structure in the PDF.
        3. CONTENT PRECISION:
           - Extract the full question text.
           - For `MSA` and `MMA`: Extract 4 options (A, B, C, D).
           - For `TOF`: The options must be exactly ["True", "False"].
           - For `FIB` and `SAQ`: Set `options` to an empty array.
        4. CORRECT ANSWER:
           - For `MSA` and `TOF`: `correct_option_index` is a single integer (0-indexed).
           - For `MMA`: `correct_option_indices` is an array of integers (e.g., [0, 2]).
           - For `FIB` and `SAQ`: `correct_answer_text` is the expected string answer.
        5. IMAGE/DIAGRAM DETECTION:
           - For any type, if visual elements are required:
             - Provide the 1-indexed `page_number`.
             - Provide a precise `image_box` [ymin, xmin, ymax, xmax] (normalized 0-1000).
        6. LANGUAGE: Preserve scripts (Hindi, Marathi, etc.) exactly.
        7. EXPLANATION: Provide a clear `solution`.

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
              "page_number": {"type": "integer"},
              "correct_option_index": {"type": "integer", "minimum": 0, "maximum": 3},
              "correct_option_indices": {"type": "array", "items": {"type": "integer"}},
              "correct_answer_text": {"type": "string"},
              "solution": {"type": "string"},
              "hint": {"type": "string"}
            },
            "required": ["type", "question", "options", "image_box", "page_number", "solution", "hint"]
          }
        }
        EOT;
    }

    /**
     * Inserts the parsed questions into the database.
     */
    private function insertQuestionsToDB($questionsData, $topicId, $userId) {
        $insertedCount = 0;
        $topic = Topic::with('skill')->find($topicId);
        if (!$topic) return 0;

        $skill = $topic->skill ?? Skill::first();
        $defaultDiff = DifficultyLevel::where('code', 'EASY')->first();

        // Cache question types
        $qTypes = QuestionType::all()->keyBy('code');

        foreach ($questionsData as $qData) {
            if (empty($qData['question'])) continue;

            $typeCode = $qData['type'] ?? 'MSA';
            $type = $qTypes->get($typeCode) ?: $qTypes->get('MSA');

            // Duplicate Check
            $exists = Question::where('topic_id', $topic->id)
                ->where('question', 'LIKE', substr($qData['question'], 0, 100).'%')
                ->exists();

            if ($exists) continue;

            DB::beginTransaction();
            try {
                $formattedOptions = [];
                $correctAnswer = null;

                switch ($typeCode) {
                    case 'MMA':
                        foreach ($qData['options'] as $optText) {
                            $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                        }
                        $indices = is_array($qData['correct_option_indices']) ? $qData['correct_option_indices'] : [];
                        $correctAnswer = array_map(fn($i) => (int)$i + 1, $indices);
                        break;
                    case 'TOF':
                    case 'MSA':
                        foreach ($qData['options'] as $optText) {
                            $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                        }
                        $correctAnswer = (isset($qData['correct_option_index']) ? (int)$qData['correct_option_index'] : 0) + 1;
                        break;
                    case 'FIB':
                    case 'SAQ':
                        $formattedOptions = []; // Logic derived from QuestionRepository::setDefaultOptions
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

                DB::commit();
                $insertedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to insert question: " . $e->getMessage());
            }
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

        $count = $this->insertQuestionsToDB($questions, $batchData['topic_id'], $batchData['user_id'] ?? 1);

        if (isset($batchData['pdf_path'])) {
            Storage::delete($batchData['pdf_path']);
        }
        Storage::delete([$metaFile, $jsonFile]);

        $request->session()->flash('success', "Successfully imported {$count} questions!");

        return response()->json(['success' => true, 'redirect' => route('admin.ai-import.index')]);
    }
}
