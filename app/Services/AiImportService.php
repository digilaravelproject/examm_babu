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
     * Uses File API for large PDFs and Structured Output for accuracy.
     *
     * @param  string  $pdfPath
     * @param  int  $startPage
     * @param  int  $endPage
     * @param  string|null $batchId
     * @param  int|null $topicId
     * @return array
     */
    public function callGeminiApi($pdfPath, $startPage = 1, $endPage = 50, $batchId = null, $topicId = null)
    {
        set_time_limit(1200);

        $apiKey = $this->settings->gemini_api_key ?: config('services.gemini.key');
        if (!$apiKey) {
            Log::error("Gemini API Key missing for Batch: {$batchId}");
            throw new \Exception('Gemini API Key not found. Please set it in Admin -> Settings -> AI Settings.');
        }

        $model = ($this->settings->model_name === 'custom') 
            ? $this->settings->custom_model 
            : ($this->settings->model_name ?: 'gemini-1.5-flash');
            
        if (!$model) {
            Log::error("Gemini Model not specified for Batch: {$batchId}");
            throw new \Exception('AI Model not specified. Please select a model in AI Settings.');
        }

        if (!file_exists($pdfPath)) {
            Log::error("PDF file missing at: {$pdfPath} for Batch: {$batchId}");
            throw new \Exception('PDF file not found at path: ' . $pdfPath);
        }

        // Use File API for PDFs > 1MB or if batchId is provided
        $fileUri = null;
        $fileName = null;

        try {
            // 1. Upload File to Gemini File API
            Log::info("Uploading PDF to Gemini File API", ['batch_id' => $batchId, 'path' => $pdfPath]);
            $uploadResult = $this->uploadToGemini($pdfPath, $apiKey);
            $fileUri = $uploadResult['file']['uri'];
            $fileName = $uploadResult['file']['name'];

            // 2. Wait for file to be processed (active state)
            Log::info("Waiting for Gemini File Processing", ['batch_id' => $batchId, 'file_name' => $fileName]);
            $this->waitForFile($fileName, $apiKey);

            // 3. Generate Content with Structured Output
            Log::info("Requesting Content Generation from Gemini", [
                'batch_id' => $batchId,
                'model' => $model,
                'pages' => "{$startPage}-{$endPage}",
                'topic_id' => $topicId
            ]);

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(1200)->withOptions(['verify' => false])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $this->getUltraEfficientPrompt($startPage, $endPage)],
                                ['fileData' => ['mimeType' => 'application/pdf', 'fileUri' => $fileUri]],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseFormat' => [
                            'text' => [
                                'mimeType' => 'application/json',
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'type' => ['type' => 'string', 'enum' => ['MSA', 'MMA', 'TOF', 'FIB', 'SAQ']],
                                            'question' => ['type' => 'string'],
                                            'options' => [
                                                'type' => 'array',
                                                'items' => ['type' => 'string']
                                            ],
                                            'correct_option_index' => ['type' => 'integer', 'description' => '0-based index for MSA/TOF'],
                                            'correct_option_indices' => [
                                                'type' => 'array',
                                                'items' => ['type' => 'integer'],
                                                'description' => '0-based indices for MMA'
                                            ],
                                            'correct_answer_text' => ['type' => 'string', 'description' => 'Text for FIB/SAQ'],
                                            'solution' => ['type' => 'string'],
                                            'hint' => ['type' => 'string'],
                                            'image_box' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'integer',
                                                    'minimum' => 0,
                                                    'maximum' => 1000
                                                ],
                                                'description' => '[ymin, xmin, ymax, xmax] coordinates (0-1000) for question image'
                                            ],
                                            'option_image_boxes' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'index' => ['type' => 'integer', 'description' => '0-based index of the option (0, 1, 2, 3...)'],
                                                        'box' => [
                                                            'type' => 'array',
                                                            'items' => [
                                                                'type' => 'integer',
                                                                'minimum' => 0,
                                                                'maximum' => 1000
                                                            ],
                                                            'description' => '[ymin, xmin, ymax, xmax] coordinates (0-1000)'
                                                        ]
                                                    ],
                                                    'required' => ['index', 'box']
                                                ],
                                                'description' => 'List of objects mapping option index to coordinates'
                                            ],
                                            'page_number_extracted' => ['type' => 'integer', 'description' => 'The page number where this question was found']
                                        ],
                                        'required' => ['type', 'question']
                                    ]
                                ]
                            ]
                        ],
                        'temperature' => 0.1,
                        'maxOutputTokens' => 8192,
                    ],
                ]);

            if (!$response->successful()) {
                $errorResponse = $response->json();
                $errorMessage = $errorResponse['error']['message'] ?? 'Unknown Gemini API error';
                
                Log::error("Gemini API Error [Batch: {$batchId}]", [
                    'status' => $response->status(),
                    'error_details' => $errorResponse,
                    'pages' => "{$startPage}-{$endPage}",
                    'batch_id' => $batchId,
                    'topic_id' => $topicId,
                    'model' => $model
                ]);

                if ($response->status() === 404 && str_contains($errorMessage, 'not found')) {
                    throw new \Exception("Gemini API Error: The selected model '{$model}' is not supported by the stable v1 API or does not exist. Please try switching to 'gemini-1.5-flash' or 'gemini-2.0-flash' in AI Settings.");
                }

                throw new \Exception("Gemini API Error: {$errorMessage}");
            }

            $result = $response->json();
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                Log::error("Gemini returned empty content for Batch: {$batchId}", ['response' => $result]);
                throw new \Exception('Empty response from AI.');
            }

            Log::info("Gemini Extraction Successful for Batch: {$batchId}", [
                'candidate_count' => count($result['candidates'] ?? []),
                'pages' => "{$startPage}-{$endPage}"
            ]);

            return $this->parseAiResponse($content);

        } catch (\Exception $e) {
            Log::error("AI Import Service Exception [Batch: {$batchId}]", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'batch_id' => $batchId,
                'topic_id' => $topicId
            ]);
            throw $e;
        } finally {
            // 4. Cleanup File from Gemini
            if ($fileName) {
                try {
                    $this->deleteFromGemini($fileName, $apiKey);
                } catch (\Exception $ex) {
                    Log::warning("Failed to delete temp file from Gemini: {$fileName}", ['error' => $ex->getMessage()]);
                }
            }
        }
    }

    /**
     * Upload file to Gemini File API (REST)
     */
    private function uploadToGemini($filePath, $apiKey)
    {
        $fileSize = filesize($filePath);
        $mimeType = 'application/pdf';
        $displayName = basename($filePath);

        // Metadata for upload
        $metadata = ['file' => ['displayName' => $displayName]];

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'X-Goog-Upload-Protocol' => 'multipart',
                'X-Goog-Upload-Command' => 'upload, finalize',
                'X-Goog-Upload-Header-Content-Length' => $fileSize,
                'X-Goog-Upload-Header-Content-Type' => $mimeType,
            ])
            ->asMultipart()
            ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$apiKey}", [
                [
                    'name' => 'metadata',
                    'contents' => json_encode($metadata),
                    'headers' => ['Content-Type' => 'application/json']
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'headers' => ['Content-Type' => $mimeType]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini File Upload Failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Wait for file to reach 'ACTIVE' state
     */
    private function waitForFile($fileName, $apiKey)
    {
        $maxAttempts = 30;
        for ($i = 0; $i < $maxAttempts; $i++) {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withOptions(['verify' => false])
                ->get("https://generativelanguage.googleapis.com/v1beta/{$fileName}?key={$apiKey}");
            
            $state = $response->json()['state'] ?? 'PROCESSING';
            if ($state === 'ACTIVE') return true;
            if ($state === 'FAILED') throw new \Exception('Gemini File Processing Failed.');
            
            sleep(2);
        }
        throw new \Exception('Gemini File Processing Timeout.');
    }

    /**
     * Delete file from Gemini File API
     */
    private function deleteFromGemini($fileName, $apiKey)
    {
        Http::withOptions(['verify' => false])
            ->delete("https://generativelanguage.googleapis.com/v1beta/{$fileName}?key={$apiKey}");
    }

    /**
     * Refined, Token-Efficient Prompt for Structured Output.
     */
    private function getUltraEfficientPrompt($startPage, $endPage)
    {
        // Build a smart page instruction
        if ($startPage == 1 && $endPage >= 50) {
            $pageInstruction = 'Extract ALL questions from ALL pages of the provided PDF.';
        } elseif ($startPage == 1) {
            $pageInstruction = "Extract ALL questions from pages 1 to {$endPage} of the provided PDF.";
        } else {
            $pageInstruction = "Extract ALL questions from pages {$startPage} to {$endPage} of the provided PDF.";
        }

        return <<<EOT
Act as an Expert Question Extractor. {$pageInstruction}
Ensure all options, correct answers, and solutions are extracted with 100% accuracy.

IMAGE DETECTION & SPATIAL COORDINATES:
1. If a question contains a diagram, graph, equation, or illustration, provide its coordinates in 'image_box' as [ymin, xmin, ymax, xmax] (normalized 0-1000).
2. IMPORTANT: If an image is detected in the question text, insert the placeholder text "[IMAGE HERE]" at the exact point in the 'question' string where the image appears.
3. If options contain images, provide coordinates in 'option_image_boxes' as a list of objects containing 'index' and 'box'. Insert "[IMAGE HERE]" in the option text.

MAPPING & TYPES:
- 'page_number_extracted': The exact physical page number from the PDF (1-indexed).
- Types: MSA (Single Choice), MMA (Multiple Choice), TOF (True/False), FIB (Fill in blanks), SAQ (Short Answer).
- Format: Return a strict JSON array matching the requested schema.
- Language: Keep the extracted text exactly as it appears in the PDF.
EOT;
    }

    /**
     * Clean and parse AI response.
     */
    private function parseAiResponse($content)
    {
        try {
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Attempt cleanup if Gemini adds markdown markers
                $cleanContent = preg_replace('/^```json\s*/i', '', trim($content));
                $cleanContent = preg_replace('/```$/', '', trim($cleanContent));
                $decoded = json_decode($cleanContent, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Gemini JSON Parsing Failed after cleanup', [
                        'error' => json_last_error_msg(),
                        'raw_content' => substr($content, 0, 1000) . '...' 
                    ]);
                    throw new \Exception('Failed to parse AI JSON response: ' . json_last_error_msg());
                }
            }

            if (!$decoded) {
                throw new \Exception('Empty or invalid decoded JSON from AI.');
            }

            return $decoded;
        } catch (\Exception $e) {
            Log::error('AI Response Parsing Exception: ' . $e->getMessage(), [
                'content_snippet' => substr($content, 0, 500)
            ]);
            throw $e;
        }
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
