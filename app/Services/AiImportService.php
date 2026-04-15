<?php

namespace App\Services;

use App\Models\DifficultyLevel;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\Topic;
use App\Repositories\QuestionRepository;
use App\Settings\AiSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiImportService
{
    protected $repository;
    protected $settings;

    public function __construct(QuestionRepository $repository, AiSettings $settings)
    {
        $this->repository = $repository;
        $this->settings = $settings;
    }

    /**
     * Call Gemini API to extract questions.
     *
     * @param  string  $pdfPath
     * @param  int  $startPage
     * @param  int  $endPage
     * @return array
     *
     * @throws \Exception
     */
    public function callGeminiApi($pdfPath, $startPage = 1, $endPage = 999)
    {
        set_time_limit(1200);

        $apiKey = $this->settings->gemini_api_key ?: config('services.gemini.key');
        if (!$apiKey) {
            throw new \Exception('Gemini API Key not found. Please set it in Admin -> Settings -> AI Settings.');
        }

        $model = ($this->settings->model_name === 'custom') 
            ? $this->settings->custom_model 
            : ($this->settings->model_name ?: 'gemini-1.5-flash');
            
        if (!$model) {
            throw new \Exception('AI Model not specified. Please select a model in AI Settings.');
        }

        if (!file_exists($pdfPath)) {
            throw new \Exception('PDF file not found at path: ' . $pdfPath);
        }

        $pdfBase64 = base64_encode(file_get_contents($pdfPath));

        $maxRetries = 3;
        $retryDelay = 2; // initial delay in seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(1200)->withOptions([
                'verify' => false,
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $this->getUltraEfficientPrompt($startPage, $endPage)],
                            [
                                'inline_data' => [
                                    'mime_type' => 'application/pdf',
                                    'data' => $pdfBase64,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.1,
                    'maxOutputTokens' => 8192,
                ],
            ]);

            if ($response->successful()) {
                break;
            }

            $errorBody = $response->json();
            $errorCode = $response->status();
            $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';

            if ($errorCode === 429 && $attempt < $maxRetries) {
                Log::warning("Gemini API Rate Limit (429) on attempt {$attempt}. Retrying in {$retryDelay} seconds...");
                sleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff

                continue;
            }

            Log::error('Gemini API Error: ' . $response->body());
            throw new \Exception('Gemini API Error: ' . $errorMessage);
        }

        $result = $response->json();
        $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$content) {
            throw new \Exception('Empty response from AI.');
        }

        return $this->parseAiResponse($content);
    }

    /**
     * Refined, Token-Efficient Prompt.
     */
    private function getUltraEfficientPrompt($startPage, $endPage)
    {
        return <<<EOT
Act as an Expert Question Extractor. Extract ALL questions from pages {$startPage} to {$endPage} of the PDF.
Output ONLY a raw JSON array. No chatter. Ensure sequence preservation.

SCHEMA:
[
  {
    "type": "MSA"|"MMA"|"TOF"|"FIB"|"SAQ",
    "question": "text",
    "options": ["A", "B", "C", "D"],
    "image_box": [ymin, xmin, ymax, xmax] | null,
    "option_image_boxes": { "0": [box], ... } | null,
    "page_number": int,
    "correct_option_index": int (0-3),
    "correct_option_indices": [int],
    "correct_answer_text": "string",
    "solution": "short string",
    "hint": "short string",
    "reasoning": "Brief internal logic for answer"
  }
]

RULES:
1. Extract in EXACT order.
2. If image present, box coords [0-1000] normalized.
3. If no answer explicitly stated, use 'reasoning' to derive correct one.
4. TOF options must be ["True", "False"]. FIB/SAQ options [].
5. Token Efficiency: Keep solution/hint < 20 words.
EOT;
    }

    /**
     * Clean and parse AI response.
     */
    private function parseAiResponse($content)
    {
        $cleanContent = preg_replace('/^```json\s*/i', '', trim($content));
        $cleanContent = preg_replace('/```$/', '', trim($cleanContent));
        $cleanContent = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $cleanContent);

        $decoded = json_decode($cleanContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error: ' . json_last_error_msg());
            // Attempt simple repair for truncated JSON
            if (strpos($cleanContent, '[') === 0 && substr($cleanContent, -1) !== ']') {
                $cleanContent = rtrim($cleanContent, ',');
                $decoded = json_decode($cleanContent . '}]', true) ?? json_decode($cleanContent . ']', true);
            }
        }

        if (!$decoded) {
            throw new \Exception('Failed to parse AI JSON response.');
        }

        return $decoded;
    }

    /**
     * Save questions with bulk optimization and transactions.
     */
    public function importQuestions(array $questionsData, int $topicId, int $userId)
    {
        return DB::transaction(function () use ($questionsData, $topicId, $userId) {
            $topic = Topic::with('skill')->findOrFail($topicId);
            $skill = $topic->skill ?? Skill::first();
            $defaultDiff = DifficultyLevel::where('code', 'EASY')->first();
            $qTypes = QuestionType::all()->keyBy('code');

            // Prevent N+1: Fetch existing for duplicate check
            $existingPrefixes = Question::where('topic_id', $topicId)
                ->get(['question'])
                ->map(fn($q) => strtolower(trim(substr(strip_tags($q->question), 0, 100))))
                ->toArray();

            $insertedCount = 0;
            $batchTime = now()->setTimezone('Asia/Kolkata')->format('Ymd_His');

            foreach ($questionsData as $index => $qData) {
                if (empty($qData['question'])) {
                    continue;
                }

                $cleanPrefix = strtolower(trim(substr(strip_tags($qData['question']), 0, 100)));
                if (in_array($cleanPrefix, $existingPrefixes)) {
                    continue;
                }

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
                        $correctAnswer = $qData['correct_answer_text'] ?? '';
                        break;
                }

                $code = 'que_ai_' . $batchTime . '_' . $skill->id . '_' . Str::random(5);

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
                    'is_active' => true,
                    'code' => $code,
                ]);

                $existingPrefixes[] = $cleanPrefix;
                $insertedCount++;
            }

            return $insertedCount;
        });
    }
}
