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

    protected array $lastImportDiagnostics = [];

    public function __construct(QuestionRepository $repository, AiSettings $settings)
    {
        $this->repository = $repository;
        $this->settings = $settings;
    }

    public function getLastImportDiagnostics(): array
    {
        return $this->lastImportDiagnostics;
    }

    public function callGeminiApi($pdfPath, $startPage = 1, $endPage = 50, $batchId = null, $topicId = null)
    {
        set_time_limit(1200);

        $apiKey = $this->settings->gemini_api_key ?: config('services.gemini.key');
        if (! $apiKey) {
            Log::error("Gemini API Key missing for Batch: {$batchId}");
            throw new \Exception('Gemini API Key not found. Please set it in Admin -> Settings -> AI Settings.');
        }

        $model = ($this->settings->model_name === 'custom')
            ? $this->settings->custom_model
            : ($this->settings->model_name ?: 'gemini-2.5-flash');

        // Fallback for older models that are no longer available to new users
        if ($model === 'gemini-2.0-flash' || $model === 'gemini-1.5-flash') {
            // Check for 2.5-flash as it's the newer recommended model for this key
            $model = 'gemini-2.5-flash';
        }

        if (! $model) {
            Log::error("Gemini Model not specified for Batch: {$batchId}");
            throw new \Exception('AI Model not specified. Please select a model in AI Settings.');
        }

        if (! file_exists($pdfPath)) {
            Log::error("PDF file missing at: {$pdfPath} for Batch: {$batchId}");
            throw new \Exception('PDF file not found at path: '.$pdfPath);
        }

        $fileUri = null;
        $fileName = null;

        try {
            Log::info('Uploading PDF to Gemini File API', ['batch_id' => $batchId, 'path' => $pdfPath]);
            $uploadResult = $this->uploadToGemini($pdfPath, $apiKey);
            $fileUri = $uploadResult['file']['uri'];
            $fileName = $uploadResult['file']['name'];

            Log::info('Waiting for Gemini File Processing', ['batch_id' => $batchId, 'file_name' => $fileName]);
            $this->waitForFile($fileName, $apiKey);

            Log::info('Requesting chunked Gemini extraction', [
                'batch_id' => $batchId,
                'model' => $model,
                'pages' => "{$startPage}-{$endPage}",
                'topic_id' => $topicId,
            ]);

            $questions = $this->extractGeminiChunks($fileUri, $apiKey, $model, $startPage, $endPage, $batchId, $topicId);

            Log::info("Gemini Extraction Successful for Batch: {$batchId}", [
                'pages' => "{$startPage}-{$endPage}",
                'final_count' => count($questions),
                'diagnostics' => $this->lastImportDiagnostics,
            ]);

            return $questions;

        } catch (\Exception $e) {
            Log::error("AI Import Service Exception [Batch: {$batchId}]", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'batch_id' => $batchId,
                'topic_id' => $topicId,
            ]);
            throw $e;
        } finally {
            if ($fileName) {
                try {
                    $this->deleteFromGemini($fileName, $apiKey);
                } catch (\Exception $ex) {
                    Log::warning("Failed to delete temp file from Gemini: {$fileName}", ['error' => $ex->getMessage()]);
                }
            }
        }
    }

    private function extractGeminiChunks(string $fileUri, string $apiKey, string $model, int $startPage, int $endPage, ?string $batchId, ?int $topicId): array
    {
        $chunkSize = max(1, (int) config('services.gemini.import_chunk_pages', 2));
        $maxRetries = max(0, (int) config('services.gemini.import_max_retries', 2));
        $minQuestionsPerChunk = max(0, (int) config('services.gemini.import_min_questions_per_chunk', 1));

        $this->lastImportDiagnostics = [
            'chunk_size' => $chunkSize,
            'max_retries' => $maxRetries,
            'page_range' => "{$startPage}-{$endPage}",
            'chunks' => [],
            'deduped' => [],
            'final_count' => 0,
        ];

        $merged = [];
        $failedChunks = [];

        for ($chunkStart = $startPage; $chunkStart <= $endPage; $chunkStart += $chunkSize) {
            $chunkEnd = min($endPage, $chunkStart + $chunkSize - 1);
            $chunkKey = "{$chunkStart}-{$chunkEnd}";
            $chunkDiagnostics = [
                'pages' => $chunkKey,
                'attempts' => [],
                'question_count' => 0,
                'status' => 'pending',
            ];

            $chunkQuestions = null;
            $lastReason = null;

            for ($attempt = 1; $attempt <= ($maxRetries + 1); $attempt++) {
                try {
                    Log::info('Gemini chunk extraction attempt', [
                        'batch_id' => $batchId,
                        'topic_id' => $topicId,
                        'pages' => $chunkKey,
                        'attempt' => $attempt,
                    ]);

                    $result = $this->requestGeminiChunk($fileUri, $apiKey, $model, $chunkStart, $chunkEnd, $attempt);
                    $normalized = $this->normalizeExtractedQuestionsForImport($result['questions'], $chunkStart, $chunkEnd);
                    $count = count($normalized);

                    $retryReason = null;
                    if ($result['finish_reason'] === 'MAX_TOKENS') {
                        $retryReason = 'MAX_TOKENS';
                    } elseif ($result['repaired']) {
                        $retryReason = 'truncated_json_repaired';
                    } elseif ($count === 0) {
                        $retryReason = 'zero_question_chunk';
                    } elseif ($count < $minQuestionsPerChunk && ($chunkEnd - $chunkStart + 1) > 0) {
                        $retryReason = 'suspiciously_low_count';
                    }

                    $chunkDiagnostics['attempts'][] = [
                        'attempt' => $attempt,
                        'count' => $count,
                        'finish_reason' => $result['finish_reason'],
                        'repaired' => $result['repaired'],
                        'retry_reason' => $retryReason,
                    ];

                    if ($retryReason && $attempt <= $maxRetries) {
                        Log::warning('Retrying Gemini chunk extraction', [
                            'batch_id' => $batchId,
                            'pages' => $chunkKey,
                            'attempt' => $attempt,
                            'reason' => $retryReason,
                            'count' => $count,
                        ]);
                        $lastReason = $retryReason;
                        continue;
                    }

                    if ($retryReason) {
                        $lastReason = $retryReason;
                        break;
                    }

                    $chunkQuestions = $normalized;
                    $chunkDiagnostics['question_count'] = $count;
                    $chunkDiagnostics['status'] = 'completed';
                    break;
                } catch (\Throwable $e) {
                    $lastReason = $e->getMessage();
                    $chunkDiagnostics['attempts'][] = [
                        'attempt' => $attempt,
                        'count' => 0,
                        'finish_reason' => null,
                        'repaired' => false,
                        'retry_reason' => 'exception: '.$e->getMessage(),
                    ];

                    if ($attempt <= $maxRetries) {
                        Log::warning('Retrying failed Gemini chunk extraction', [
                            'batch_id' => $batchId,
                            'pages' => $chunkKey,
                            'attempt' => $attempt,
                            'error' => $e->getMessage(),
                        ]);
                        continue;
                    }
                }
            }

            if ($chunkQuestions === null) {
                $chunkDiagnostics['status'] = 'failed';
                $chunkDiagnostics['failure_reason'] = $lastReason ?: 'unknown_failure';
                $failedChunks[] = $chunkDiagnostics;
            } else {
                $merged = array_merge($merged, $chunkQuestions);
            }

            $this->lastImportDiagnostics['chunks'][] = $chunkDiagnostics;

            Log::info('Gemini chunk extraction finished', [
                'batch_id' => $batchId,
                'pages' => $chunkKey,
                'status' => $chunkDiagnostics['status'],
                'question_count' => $chunkDiagnostics['question_count'],
                'attempts' => count($chunkDiagnostics['attempts']),
            ]);
        }

        if ($failedChunks) {
            $this->lastImportDiagnostics['failed_chunks'] = $failedChunks;
            throw new \RuntimeException('Import incomplete: failed to extract all detectable questions. Failed chunks: '.json_encode($failedChunks));
        }

        $deduped = $this->dedupeExtractedQuestions($merged, $batchId);
        $this->lastImportDiagnostics['final_count'] = count($deduped);

        if (count($deduped) === 0) {
            throw new \RuntimeException('Import incomplete: no questions were extracted from the selected PDF range.');
        }

        return $deduped;
    }

    private function requestGeminiChunk(string $fileUri, string $apiKey, string $model, int $startPage, int $endPage, int $attempt): array
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(1800)->withOptions(['verify' => false])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $this->getUltraEfficientPrompt($startPage, $endPage, $attempt)],
                            ['fileData' => ['mimeType' => 'application/pdf', 'fileUri' => $fileUri]],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->geminiQuestionResponseSchema(),
                    'temperature' => 0.1,
                    'maxOutputTokens' => 30000,
                ],
            ]);

        if (! $response->successful()) {
            $this->handleGeminiError($response, $model);
        }

        $result = $response->json();
        $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        $finishReason = $result['candidates'][0]['finishReason'] ?? null;

        if (! $content) {
            Log::error('Gemini returned empty chunk content', ['response' => $result]);
            throw new \Exception('Empty response from AI.');
        }

        $parsed = $this->parseAiResponseWithMeta($content);

        return [
            'questions' => $parsed['questions'],
            'repaired' => $parsed['repaired'],
            'finish_reason' => $finishReason,
        ];
    }

    private function handleGeminiError($response, string $model): void
    {
        $errorResponse = $response->json();
        $errorMessage = $errorResponse['error']['message'] ?? 'Unknown Gemini API error';

        Log::error('Gemini API Error', [
            'status' => $response->status(),
            'error_details' => $errorResponse,
            'model' => $model,
        ]);

        if ($response->status() === 404 && str_contains($errorMessage, 'not found')) {
            $availableStr = 'Unknown';
            try {
                $apiKey = $this->settings->gemini_api_key ?: config('services.gemini.key');
                $modelsResponse = Http::timeout(10)->withOptions(['verify' => false])
                    ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
                if ($modelsResponse->successful()) {
                    $models = $modelsResponse->json()['models'] ?? [];
                    $names = array_map(function ($m) {
                        return str_replace('models/', '', $m['name'] ?? '');
                    }, $models);
                    $availableStr = implode(', ', array_filter($names));
                } else {
                    $availableStr = 'Failed to list models: '.($modelsResponse->json()['error']['message'] ?? 'Unknown API error');
                }
            } catch (\Exception $ex) {
                $availableStr = 'Fetch error: '.$ex->getMessage();
            }

            throw new \Exception("Gemini API Error: The selected model '{$model}' was not found. Available models for your API key: {$availableStr}. Please update AI Settings with one of these.");
        }

        throw new \Exception("Gemini API Error: {$errorMessage}");
    }

    private function geminiQuestionResponseSchema(): array
    {
        return [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'type' => [
                        'type' => 'STRING',
                        'enum' => ['MSA', 'MMA', 'TOF', 'FIB', 'SAQ'],
                    ],
                    'question_number' => ['type' => 'STRING'],
                    'question' => ['type' => 'STRING'],
                    'options' => [
                        'type' => 'ARRAY',
                        'items' => ['type' => 'STRING'],
                    ],
                    'correct_option_index' => ['type' => 'INTEGER'],
                    'correct_option_indices' => [
                        'type' => 'ARRAY',
                        'items' => ['type' => 'INTEGER'],
                    ],
                    'correct_answer_text' => ['type' => 'STRING'],
                    'solution' => ['type' => 'STRING'],
                    'hint' => ['type' => 'STRING'],
                    'image_box' => [
                        'type' => 'ARRAY',
                        'items' => ['type' => 'NUMBER', 'minimum' => 0, 'maximum' => 1000],
                    ],
                    'option_image_boxes' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'index' => ['type' => 'INTEGER'],
                                'box' => [
                                    'type' => 'ARRAY',
                                    'items' => ['type' => 'NUMBER', 'minimum' => 0, 'maximum' => 1000],
                                ],
                            ],
                        ],
                    ],
                    'page_number' => ['type' => 'INTEGER'],
                    'page_number_extracted' => ['type' => 'INTEGER'],
                    'source_page' => ['type' => 'INTEGER'],
                ],
                'required' => ['type', 'question'],
            ],
        ];
    }

    private function uploadToGemini($filePath, $apiKey)
    {
        $fileSize = filesize($filePath);
        $mimeType = 'application/pdf';
        $displayName = basename($filePath);

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
                    'headers' => ['Content-Type' => 'application/json'],
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'headers' => ['Content-Type' => $mimeType],
                ],
            ]);

        if (! $response->successful()) {
            throw new \Exception('Gemini File Upload Failed: '.$response->body());
        }

        return $response->json();
    }

    private function waitForFile($fileName, $apiKey)
    {
        $maxAttempts = 30;
        for ($i = 0; $i < $maxAttempts; $i++) {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withOptions(['verify' => false])
                ->get("https://generativelanguage.googleapis.com/v1beta/{$fileName}?key={$apiKey}");

            $state = $response->json()['state'] ?? 'PROCESSING';
            if ($state === 'ACTIVE') {
                return true;
            }
            if ($state === 'FAILED') {
                throw new \Exception('Gemini File Processing Failed.');
            }

            sleep(2);
        }
        throw new \Exception('Gemini File Processing Timeout.');
    }

    private function deleteFromGemini($fileName, $apiKey)
    {
        Http::withOptions(['verify' => false])
            ->delete("https://generativelanguage.googleapis.com/v1beta/{$fileName}?key={$apiKey}");
    }

    private function getUltraEfficientPrompt($startPage, $endPage, int $attempt = 1)
    {
        $pageInstruction = ($startPage == 1 && $endPage >= 50) 
            ? "Extract EVERYTHING from ALL pages." 
            : "Extract EVERYTHING from pages {$startPage} to {$endPage}.";
        $retryInstruction = $attempt > 1
            ? "\n### RETRY MODE\nA previous attempt for this exact page range was incomplete, malformed, empty, or too short. Re-scan every line and return a complete JSON array for this page range only."
            : '';

        return <<<EOT
### ROLE: LEAD DATA ARCHITECT & EXAM SPECIALIST
### MISSION: 100% RAW FIDELITY EXTRACTION
{$pageInstruction}
{$retryInstruction}

---
### 1. RIGOROUS EXTRACTION PROTOCOL (NO SKIPPING)
- **Zero Omission Policy:** You are FORBIDDEN from skipping any question, sub-question, or part. 
- **Sequential Scan:** Scan the document line-by-line. If you find a question marker (e.g., 1., Q2, i), a), etc.), you MUST extract it.
- **High Density:** Even if a page has 20+ small questions, extract every single one. Use your massive token limit (30,000) to provide the full list.
- **Math & Science:** Use LaTeX syntax for EVERY formula, equation, or scientific symbol (e.g., use $ \frac{-b \pm \sqrt{b^2-4ac}}{2a} $ or $ H_{2}O $).
- **Question Number:** If a visible question number/marker exists, return it in `question_number`.

---
### 2. SPATIAL & IMAGE INTELLIGENCE
- **Coordinate Precision:** For ANY diagram, graph, map, or complex illustration, provide pixel-perfect [ymin, xmin, ymax, xmax] boxes (0-1000).
- **Placeholder Injection:** In the 'question' or 'options' text, insert "[IMAGE HERE]" at the EXACT location where the visual element appears relative to the text.
- **Option Images:** If options are images (common in geometry), you MUST provide 'option_image_boxes' as objects like {"index":0,"box":[ymin,xmin,ymax,xmax]}.
- **Coordinate Format:** All image boxes must be [ymin, xmin, ymax, xmax], normalized from 0 to 1000 for the full PDF page.

---
### 3. DATA STRUCTURE & MAPPING
- **page_number_extracted:** The physical page number in the PDF (1-indexed).
- **type:**
  - MSA: Single correct answer.
  - MMA: Multiple correct answers.
  - TOF: True/False.
  - FIB: Fill in the blank (Provide correct_answer_text).
  - SAQ: Descriptive/Short Answer (Provide detailed solution/solution).
- **Correctness:** Analyze the text deeply to identify the correct option. If the PDF has an answer key at the end, use it!

---
### 4. OUTPUT FORMAT
Return a STRICT JSON ARRAY of objects. No preamble, no commentary. Just raw, high-fidelity data.
EOT;
    }

    private function parseAiResponse($content)
    {
        return $this->parseAiResponseWithMeta($content)['questions'];
    }

    private function parseAiResponseWithMeta($content): array
    {
        $cleanContent = trim($content);
        $repaired = false;
        
        // Remove Markdown code blocks if present
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $cleanContent, $matches)) {
            $cleanContent = trim($matches[1]);
        }

        try {
            $decoded = json_decode($cleanContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Attempt to fix truncated JSON
                $fixedContent = $this->repairTruncatedJson($cleanContent);
                $decoded = json_decode($fixedContent, true);
                $repaired = true;

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Gemini JSON Parsing Failed after repair attempt', [
                        'error' => json_last_error_msg(),
                        'raw_content_preview' => substr($content, 0, 1000).'...',
                        'last_chars' => substr($cleanContent, -50),
                    ]);
                    throw new \Exception('Failed to parse AI JSON response: '.json_last_error_msg());
                }
            }

            if (! $decoded || !is_array($decoded)) {
                throw new \Exception('Invalid JSON structure from AI (Expected Array).');
            }

            if ($repaired || $this->looksTruncatedJson($cleanContent)) {
                Log::warning('Gemini JSON response required repair or looked truncated', [
                    'repaired' => $repaired,
                    'last_chars' => substr($cleanContent, -80),
                ]);
                $repaired = true;
            }

            return [
                'questions' => $decoded,
                'repaired' => $repaired,
            ];
        } catch (\Exception $e) {
            Log::error('AI Response Parsing Exception: '.$e->getMessage(), [
                'content_snippet' => substr($content, 0, 1000),
            ]);
            throw $e;
        }
    }

    /**
     * Repairs truncated JSON by closing open brackets and braces.
     */
    private function repairTruncatedJson($json)
    {
        // Remove trailing comma if present (common in truncated arrays)
        $json = preg_replace('/,\s*$/', '', trim($json));
        
        $len = strlen($json);
        $stack = [];
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < $len; $i++) {
            $char = $json[$i];

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                continue;
            }

            if (!$inString) {
                if ($char === '{' || $char === '[') {
                    $stack[] = $char;
                } elseif ($char === '}') {
                    if (end($stack) === '{') array_pop($stack);
                } elseif ($char === ']') {
                    if (end($stack) === '[') array_pop($stack);
                }
            }
        }

        if ($inString) {
            $json .= '"'; // Close open string
        }

        // Close brackets/braces in reverse order
        while (!empty($stack)) {
            $opener = array_pop($stack);
            if ($opener === '{') {
                $json .= '}';
            } elseif ($opener === '[') {
                $json .= ']';
            }
        }

        return $json;
    }

    private function looksTruncatedJson(string $json): bool
    {
        $trimmed = rtrim($json);
        return $trimmed !== '' && ! str_ends_with($trimmed, ']');
    }

    public function normalizeExtractedQuestionsForImport(array $questions, int $chunkStart = 1, int $chunkEnd = 1): array
    {
        $normalized = [];

        foreach ($questions as $index => $question) {
            if (! is_array($question)) {
                continue;
            }

            $sourcePage = $question['source_page']
                ?? $question['page_number_extracted']
                ?? $question['page_number']
                ?? null;

            if ($sourcePage !== null) {
                $sourcePage = (int) $sourcePage;
            }

            if (! $sourcePage || $sourcePage < $chunkStart || $sourcePage > $chunkEnd) {
                $sourcePage = $chunkStart;
            }

            $question['source_page'] = $sourcePage;
            $question['page_number_extracted'] = $sourcePage;
            $question['question_number'] = isset($question['question_number'])
                ? trim((string) $question['question_number'])
                : $this->guessQuestionNumber($question['question'] ?? '', $index);

            $question['image_box'] = $this->normalizeImageBox($question['image_box'] ?? null);
            if ($question['image_box'] === null) {
                unset($question['image_box']);
            }

            $optionBoxes = $this->normalizeOptionImageBoxes($question['option_image_boxes'] ?? []);
            $question['option_image_boxes'] = $optionBoxes;

            $normalized[] = $question;
        }

        return $normalized;
    }

    public function normalizeImageBox($box): ?array
    {
        if (is_object($box)) {
            $box = (array) $box;
        }

        if (! is_array($box) || count($box) !== 4) {
            return null;
        }

        $box = array_values($box);
        foreach ($box as $value) {
            if (! is_numeric($value)) {
                return null;
            }
        }

        [$ymin, $xmin, $ymax, $xmax] = array_map(fn ($value) => (float) $value, $box);

        if ($ymin < 0 || $xmin < 0 || $ymax > 1000 || $xmax > 1000 || $ymin >= $ymax || $xmin >= $xmax) {
            return null;
        }

        return [
            (int) round($ymin),
            (int) round($xmin),
            (int) round($ymax),
            (int) round($xmax),
        ];
    }

    public function normalizeOptionImageBoxes($boxes): array
    {
        if (is_object($boxes)) {
            $boxes = (array) $boxes;
        }

        if (! is_array($boxes)) {
            return [];
        }

        $normalized = [];

        foreach ($boxes as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }

            if (is_array($value) && array_key_exists('index', $value) && array_key_exists('box', $value)) {
                $optionIndex = (int) $value['index'];
                $box = $this->normalizeImageBox($value['box']);
            } else {
                $optionIndex = is_numeric($key) ? (int) $key : null;
                $box = $this->normalizeImageBox($value);
            }

            if ($optionIndex !== null && $box !== null) {
                $normalized[(string) $optionIndex] = $box;
            }
        }

        ksort($normalized, SORT_NUMERIC);

        return $normalized;
    }

    private function guessQuestionNumber(string $question, int $index): string
    {
        $plain = trim(strip_tags($question));
        if (preg_match('/^(?:Q\.?\s*)?(\d{1,4}|[ivxlcdm]+|[a-z])[\.\)\-:]/i', $plain, $matches)) {
            return (string) $matches[1];
        }

        return '';
    }

    private function dedupeExtractedQuestions(array $questions, ?string $batchId): array
    {
        $seen = [];
        $deduped = [];

        foreach ($questions as $index => $question) {
            $identity = $this->questionIdentity($question);

            if ($identity && isset($seen[$identity])) {
                $dedupeLog = [
                    'batch_id' => $batchId,
                    'removed_index' => $index,
                    'kept_index' => $seen[$identity],
                    'source_page' => $question['source_page'] ?? null,
                    'question_number' => $question['question_number'] ?? null,
                    'identity' => $identity,
                ];
                $this->lastImportDiagnostics['deduped'][] = $dedupeLog;
                Log::info('AI import deduped exact composite duplicate', $dedupeLog);
                continue;
            }

            if ($identity) {
                $seen[$identity] = count($deduped);
            }

            $deduped[] = $question;
        }

        return $deduped;
    }

    private function questionIdentity(array $question): ?string
    {
        $sourcePage = $question['source_page'] ?? null;
        $questionNumber = trim((string) ($question['question_number'] ?? ''));
        $text = $this->normalizeQuestionText($question['question'] ?? '');

        if (! $sourcePage || $questionNumber === '' || $text === '') {
            return null;
        }

        return $sourcePage.'|'.$questionNumber.'|'.$text;
    }

    private function normalizeQuestionText(string $text): string
    {
        $text = strtolower(trim(strip_tags($text)));
        $text = preg_replace('/\s+/', ' ', $text);

        return Str::limit($text, 180, '');
    }

    public function importQuestions(array $questionsData, int $topicId, int $userId)
    {
        return DB::transaction(function () use ($questionsData, $topicId, $userId) {
            $topic = Topic::with('skill')->findOrFail($topicId);
            $skill = $topic->skill ?? Skill::first();
            $defaultDiff = DifficultyLevel::where('code', 'EASY')->first();
            $qTypes = QuestionType::all()->keyBy('code');

            $existingPrefixes = Question::where('topic_id', $topicId)
                ->get(['question'])
                ->map(fn ($q) => strtolower(trim(substr(strip_tags($q->question), 0, 100))))
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
                        foreach (($qData['options'] ?? []) as $optText) {
                            $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                        }
                        $indices = is_array($qData['correct_option_indices'] ?? null) ? $qData['correct_option_indices'] : [];
                        $correctAnswer = array_map(fn ($i) => (int) $i + 1, $indices);
                        break;
                    case 'TOF':
                    case 'MSA':
                        foreach (($qData['options'] ?? []) as $optText) {
                            $formattedOptions[] = ['option' => $optText, 'partial_weightage' => 0];
                        }
                        $correctAnswer = (isset($qData['correct_option_index']) ? (int) $qData['correct_option_index'] : 0) + 1;
                        break;
                    case 'FIB':
                    case 'SAQ':
                        $correctAnswer = $qData['correct_answer_text'] ?? '';
                        break;
                }

                $code = 'que_ai_'.$batchTime.'_'.$skill->id.'_'.Str::random(5);

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

            Log::info('AI import final DB insert count', [
                'topic_id' => $topicId,
                'user_id' => $userId,
                'input_count' => count($questionsData),
                'inserted_count' => $insertedCount,
            ]);

            return $insertedCount;
        });
    }
}
