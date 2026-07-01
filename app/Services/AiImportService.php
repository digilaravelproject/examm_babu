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
    private const IMAGE_BOX_PADDING = 6;

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

    public function callGeminiApi($pdfPath, $startPage = 1, $endPage = 50, $batchId = null, $topicId = null, callable $progressCallback = null)
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

        if ($model === 'gemini-2.0-flash' || $model === 'gemini-1.5-flash') {
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

        try {
            Log::info('Requesting chunked Gemini extraction with FPDI slicing', [
                'batch_id' => $batchId,
                'model' => $model,
                'pages' => "{$startPage}-{$endPage}",
                'topic_id' => $topicId,
            ]);

            $questions = $this->extractGeminiChunks($pdfPath, $apiKey, $model, $startPage, $endPage, $batchId, $topicId, $progressCallback);

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
        }
    }

    public function slicePdf(string $sourcePdf, int $startPage, int $endPage): string
    {
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePdf);
        
        $endPage = min($endPage, $pageCount);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $templateId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
        
        $chunkPath = tempnam(sys_get_temp_dir(), 'pdf_chunk_') . '.pdf';
        $pdf->Output('F', $chunkPath);
        return $chunkPath;
    }

    private function extractGeminiChunks(string $pdfPath, string $apiKey, string $model, int $startPage, int $endPage, ?string $batchId, ?int $topicId, callable $progressCallback = null): array
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
            'validation' => $this->defaultValidationDiagnostics(),
        ];

        $merged = [];
        $failedChunks = [];
        $totalPagesToProcess = max(1, $endPage - $startPage + 1);
        $processedPages = 0;

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
            $slicedPdfPath = null;
            $fileName = null;

            try {
                // 1. Slice the PDF for this chunk
                $slicedPdfPath = $this->slicePdf($pdfPath, $chunkStart, $chunkEnd);
                
                // 2. Upload the sliced PDF to Gemini
                $uploadResult = $this->uploadToGemini($slicedPdfPath, $apiKey);
                $fileUri = $uploadResult['file']['uri'];
                $fileName = $uploadResult['file']['name'];
                
                $this->waitForFile($fileName, $apiKey);

                for ($attempt = 1; $attempt <= ($maxRetries + 1); $attempt++) {
                    try {
                        Log::info('Gemini chunk extraction attempt', [
                            'batch_id' => $batchId,
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
                            continue;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $lastReason = 'Upload/Slice Error: ' . $e->getMessage();
            } finally {
                // Cleanup temp Gemini file
                if ($fileName) {
                    try {
                        $this->deleteFromGemini($fileName, $apiKey);
                    } catch (\Exception $ex) {
                        Log::warning("Failed to delete temp file from Gemini", ['error' => $ex->getMessage()]);
                    }
                }
                // Cleanup local temp file
                if ($slicedPdfPath && file_exists($slicedPdfPath)) {
                    @unlink($slicedPdfPath);
                }
            }

            if ($chunkQuestions === null) {
                $chunkDiagnostics['status'] = 'failed';
                $chunkDiagnostics['failure_reason'] = $lastReason ?: 'unknown_failure';
                $failedChunks[] = $chunkDiagnostics;
                Log::warning('Chunk failed, skipping', ['pages' => $chunkKey, 'reason' => $lastReason]);
                // Do NOT throw exception here, just continue to next chunk
            } else {
                $merged = array_merge($merged, $chunkQuestions);
            }

            $this->lastImportDiagnostics['chunks'][] = $chunkDiagnostics;
            
            $processedPages += ($chunkEnd - $chunkStart + 1);
            if ($progressCallback) {
                $percent = min(95, 10 + round(($processedPages / $totalPagesToProcess) * 85)); // From 10 to 95
                $progressCallback($percent, "Processed pages {$chunkStart} to {$chunkEnd}...");
            }
        }

        if ($failedChunks) {
            $this->lastImportDiagnostics['failed_chunks'] = $failedChunks;
            Log::warning('Import had failed chunks, but continuing with extracted data.', ['failed_chunks' => $failedChunks]);
        }

        $deduped = $this->dedupeExtractedQuestions($merged, $batchId);
        $this->lastImportDiagnostics['final_count'] = count($deduped);
        $this->finalizeValidationDiagnostics($deduped);

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
                        'enum' => ['MSA', 'MMA', 'TOF', 'FIB', 'SAQ', 'MTF', 'ORD', 'LAQ'],
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
                    'correct_option_label' => ['type' => 'STRING'],
                    'correct_option_text' => ['type' => 'STRING'],
                    'correct_answer' => ['type' => 'STRING'],
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
- **Math Fidelity:** Preserve equations exactly as written. Never simplify, rewrite, or convert formula notation into plain text.
- **Question Number:** If a visible question number/marker exists, return it in `question_number`.
- **Multilingual Papers:** If the same content appears in multiple languages, return ONLY English text. Do not mix scripts in a single question or option.

---
### 2. SPATIAL & IMAGE INTELLIGENCE
- **Coordinate Precision:** For ANY diagram, graph, map, or complex illustration, provide [ymin, xmin, ymax, xmax] boxes (0-1000) with a small safe padding margin around the visual.
- **Placeholder Injection:** In the 'question' or 'options' text, you MUST insert a markdown image tag `![Diagram](IMAGE_HERE)` at the EXACT location where the visual element appears.
- **Option Images:** If options are images (common in geometry), you MUST provide 'option_image_boxes' as objects like {"index":0,"box":[ymin,xmin,ymax,xmax]}.
- **Coordinate Format:** All image boxes must be [ymin, xmin, ymax, xmax], normalized from 0 to 1000 for the full PDF page.
- **Ownership:** Keep image ownership strict. Question-level image belongs only to the question field; option image belongs only to its specific option index.

---
### 3. DATA STRUCTURE & MAPPING
- **page_number_extracted:** The physical page number in the PDF (1-indexed).
- **type:**
  - MSA: Single correct answer.
  - MMA: Multiple correct answers.
  - TOF: True/False.
  - FIB: Fill in the blank (Provide correct_answer_text).
  - SAQ: Descriptive/Short Answer.
  - MTF: Match the Following.
  - ORD: Ordering/Sequence.
  - LAQ: Long Answer/Subjective.
- **Correctness:** Analyze the text deeply to identify the correct option. If the PDF has an answer key at the end, use it!
- **MCQ Answer Mapping:** For MSA/TOF, return exactly one correct option using `correct_option_index` as a 0-based index. Also include `correct_option_label` (A/B/C/D) or `correct_option_text` when visible.
- **Multiple Answer Mapping:** For MMA, return all correct options in `correct_option_indices` as 0-based indices.
- **Do Not Guess:** If the answer is unclear or absent, leave the correct answer fields empty so the import can flag it for review.

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
        $this->ensureValidationDiagnosticsInitialized();
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
            $rawType = strtoupper(trim((string) ($question['type'] ?? '')));
            $question['type'] = $this->resolveCanonicalQuestionType($question);
            $question['question_number'] = isset($question['question_number'])
                ? trim((string) $question['question_number'])
                : $this->guessQuestionNumber($question['question'] ?? '', $index);
            if ($rawType !== '' && $rawType !== $question['type']) {
                $this->lastImportDiagnostics['validation']['type_corrections'][] = [
                    'from' => $rawType,
                    'to' => $question['type'],
                    'source_page' => $sourcePage,
                    'question_number' => $question['question_number'] ?? null,
                ];
            }

            $languageFiltered = $this->filterQuestionToEnglishOnly($question);
            if ($languageFiltered === null) {
                $this->lastImportDiagnostics['validation']['english_filter']['questions_dropped']++;
                continue;
            }

            $question = $languageFiltered;
            $question['image_box'] = $this->normalizeImageBox($question['image_box'] ?? null);
            if ($question['image_box'] === null) {
                unset($question['image_box']);
            }

            $optionBoxes = $this->normalizeOptionImageBoxes($question['option_image_boxes'] ?? []);
            $question['option_image_boxes'] = $optionBoxes;
            $question = $this->normalizeCorrectAnswerFields($question);

            $this->updateValidationDiagnosticsForQuestion($question);
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

        $ymin -= self::IMAGE_BOX_PADDING;
        $xmin -= self::IMAGE_BOX_PADDING;
        $ymax += self::IMAGE_BOX_PADDING;
        $xmax += self::IMAGE_BOX_PADDING;

        $ymin = max(0, min(1000, $ymin));
        $xmin = max(0, min(1000, $xmin));
        $ymax = max(0, min(1000, $ymax));
        $xmax = max(0, min(1000, $xmax));

        if ($ymin >= $ymax || $xmin >= $xmax) {
            return null;
        }

        return [
            (int) round($ymin),
            (int) round($xmin),
            (int) round($ymax),
            (int) round($xmax),
        ];
    }

    private function resolveCanonicalQuestionType(array $question): string
    {
        $allowed = ['MSA', 'MMA', 'TOF', 'FIB', 'SAQ', 'MTF', 'ORD', 'LAQ'];
        $rawType = strtoupper(trim((string) ($question['type'] ?? '')));
        $questionText = strtolower(strip_tags((string) ($question['question'] ?? '')));

        if (in_array($rawType, $allowed, true)) {
            return $rawType;
        }

        if (preg_match('/\b(match(\s+the)?\s+following|column\s*i|column\s*ii)\b/i', $rawType.' '.$questionText)) {
            return 'MTF';
        }

        if (preg_match('/\b(arrange|order|ordering|sequence)\b/i', $rawType.' '.$questionText)) {
            return 'ORD';
        }

        if (preg_match('/\b(assertion|reason)\b/i', $rawType.' '.$questionText)) {
            return 'MSA';
        }

        if (preg_match('/\b(long\s*answer|subjective|descriptive|essay)\b/i', $rawType.' '.$questionText)) {
            return 'LAQ';
        }

        if (preg_match('/\bfill\s*in\b|_{3,}/i', $rawType.' '.$questionText)) {
            return 'FIB';
        }

        if (preg_match('/\btrue\s*\/?\s*false\b/i', $rawType.' '.$questionText)) {
            return 'TOF';
        }

        return 'SAQ';
    }

    private function filterQuestionToEnglishOnly(array $question): ?array
    {
        $textFields = ['question', 'solution', 'hint', 'correct_answer_text'];
        $mixedDetected = false;

        foreach ($textFields as $field) {
            if (! isset($question[$field]) || ! is_string($question[$field])) {
                continue;
            }

            $filtered = $this->filterEnglishText($question[$field]);
            $mixedDetected = $mixedDetected || $filtered['mixed_detected'];

            if ($filtered['uncertain']) {
                $this->lastImportDiagnostics['validation']['english_filter']['mixed_blocks_detected']++;
                return null;
            }

            if ($filtered['changed']) {
                $question[$field] = $filtered['text'];
            }
        }

        $filteredOptions = [];
        $optionsFilteredCount = 0;

        foreach (array_values($question['options'] ?? []) as $option) {
            $optionText = is_array($option)
                ? (string) ($option['option'] ?? $option['text'] ?? '')
                : (string) $option;

            $filteredOption = $this->filterEnglishText($optionText);
            $mixedDetected = $mixedDetected || $filteredOption['mixed_detected'];

            if ($filteredOption['uncertain']) {
                $this->lastImportDiagnostics['validation']['english_filter']['mixed_blocks_detected']++;
                return null;
            }

            if ($filteredOption['text'] === '') {
                $optionsFilteredCount++;
                continue;
            }

            if (is_array($option)) {
                $option['option'] = $filteredOption['text'];
                $filteredOptions[] = $option;
            } else {
                $filteredOptions[] = $filteredOption['text'];
            }
        }

        if (isset($question['options']) && is_array($question['options'])) {
            $question['options'] = $filteredOptions;
        }

        if ($optionsFilteredCount > 0) {
            $this->lastImportDiagnostics['validation']['english_filter']['options_filtered'] += $optionsFilteredCount;
        }

        if ($mixedDetected) {
            $this->lastImportDiagnostics['validation']['english_filter']['mixed_blocks_detected']++;
        }

        return $question;
    }

    private function filterEnglishText(string $text): array
    {
        $asLines = preg_replace('/<br\s*\/?>/i', "\n", str_replace(["\r\n", "\r"], "\n", $text));
        $lines = preg_split('/\n+/', (string) $asLines, -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($lines) || count($lines) === 0) {
            return ['text' => trim($text), 'changed' => false, 'uncertain' => false, 'mixed_detected' => false];
        }

        $kept = [];
        $removed = false;
        $uncertain = false;
        $mixedDetected = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $hasLatin = (bool) preg_match('/[A-Za-z]/u', $line);
            $hasRegionalScript = (bool) preg_match('/[\p{Devanagari}\p{Bengali}\p{Gujarati}\p{Gurmukhi}\p{Tamil}\p{Telugu}\p{Kannada}\p{Malayalam}\p{Oriya}]/u', $line);

            if ($hasLatin && $hasRegionalScript) {
                $uncertain = true;
                $mixedDetected = true;
                continue;
            }

            if ($hasRegionalScript && ! $hasLatin) {
                $removed = true;
                continue;
            }

            if (! $hasLatin && ! $this->looksMathContent($line) && preg_match('/[\p{L}]/u', $line)) {
                $removed = true;
                continue;
            }

            $kept[] = $line;
        }

        $filteredText = trim(implode("\n", $kept));

        return [
            'text' => $filteredText,
            'changed' => $filteredText !== trim($text),
            'uncertain' => $uncertain,
            'mixed_detected' => $mixedDetected || $removed,
        ];
    }

    private function looksMathContent(string $text): bool
    {
        return (bool) preg_match('/\\\\[a-zA-Z]+|[\^\_\=\+\-\*\/<>≤≥≠≈±√∑∫∞]|[0-9]+\s*[\+\-\*\/]\s*[0-9]/u', $text);
    }

    private function ensureValidationDiagnosticsInitialized(): void
    {
        if (! isset($this->lastImportDiagnostics['validation']) || ! is_array($this->lastImportDiagnostics['validation'])) {
            $this->lastImportDiagnostics['validation'] = $this->defaultValidationDiagnostics();
            return;
        }

        $this->lastImportDiagnostics['validation'] = array_replace_recursive(
            $this->defaultValidationDiagnostics(),
            $this->lastImportDiagnostics['validation']
        );
    }

    private function defaultValidationDiagnostics(): array
    {
        return [
            'total_extracted_count' => 0,
            'numbering' => [
                'checked' => 0,
                'is_continuous' => true,
                'missing_numbers' => [],
            ],
            'type_distribution' => [],
            'type_corrections' => [],
            'english_filter' => [
                'questions_retained' => 0,
                'questions_dropped' => 0,
                'options_filtered' => 0,
                'mixed_blocks_detected' => 0,
            ],
            'image_mapping' => [
                'questions_with_image' => 0,
                'options_with_image' => 0,
            ],
            'math_preservation' => [
                'items_with_math' => 0,
                'items_with_latex' => 0,
            ],
        ];
    }

    private function updateValidationDiagnosticsForQuestion(array $question): void
    {
        $type = strtoupper((string) ($question['type'] ?? 'SAQ'));
        $this->lastImportDiagnostics['validation']['english_filter']['questions_retained']++;
        $this->lastImportDiagnostics['validation']['type_distribution'][$type] =
            ($this->lastImportDiagnostics['validation']['type_distribution'][$type] ?? 0) + 1;

        if (isset($question['image_box'])) {
            $this->lastImportDiagnostics['validation']['image_mapping']['questions_with_image']++;
        }
        $this->lastImportDiagnostics['validation']['image_mapping']['options_with_image'] += count($question['option_image_boxes'] ?? []);

        $textFields = [
            (string) ($question['question'] ?? ''),
            (string) ($question['solution'] ?? ''),
            (string) ($question['hint'] ?? ''),
            (string) ($question['correct_answer_text'] ?? ''),
        ];

        foreach ($question['options'] ?? [] as $option) {
            $textFields[] = is_array($option)
                ? (string) ($option['option'] ?? $option['text'] ?? '')
                : (string) $option;
        }

        foreach ($textFields as $text) {
            if ($text === '') {
                continue;
            }
            if ($this->looksMathContent($text)) {
                $this->lastImportDiagnostics['validation']['math_preservation']['items_with_math']++;
            }
            if (preg_match('/\\\\[a-zA-Z]+/', $text)) {
                $this->lastImportDiagnostics['validation']['math_preservation']['items_with_latex']++;
            }
        }
    }

    private function finalizeValidationDiagnostics(array $questions): void
    {
        $this->ensureValidationDiagnosticsInitialized();
        $this->lastImportDiagnostics['validation']['total_extracted_count'] = count($questions);
        $this->lastImportDiagnostics['validation']['numbering'] = $this->buildNumberingContinuityDiagnostics($questions);

        // Recalculate final type/image/math from the deduped output snapshot.
        $this->lastImportDiagnostics['validation']['type_distribution'] = [];
        $this->lastImportDiagnostics['validation']['image_mapping'] = [
            'questions_with_image' => 0,
            'options_with_image' => 0,
        ];
        $this->lastImportDiagnostics['validation']['math_preservation'] = [
            'items_with_math' => 0,
            'items_with_latex' => 0,
        ];

        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $type = strtoupper((string) ($question['type'] ?? 'SAQ'));
            $this->lastImportDiagnostics['validation']['type_distribution'][$type] =
                ($this->lastImportDiagnostics['validation']['type_distribution'][$type] ?? 0) + 1;

            if (isset($question['image_box'])) {
                $this->lastImportDiagnostics['validation']['image_mapping']['questions_with_image']++;
            }
            $this->lastImportDiagnostics['validation']['image_mapping']['options_with_image'] += count($question['option_image_boxes'] ?? []);

            $textFields = [
                (string) ($question['question'] ?? ''),
                (string) ($question['solution'] ?? ''),
                (string) ($question['hint'] ?? ''),
                (string) ($question['correct_answer_text'] ?? ''),
            ];

            foreach ($question['options'] ?? [] as $option) {
                $textFields[] = is_array($option)
                    ? (string) ($option['option'] ?? $option['text'] ?? '')
                    : (string) $option;
            }

            foreach ($textFields as $text) {
                if ($text === '') {
                    continue;
                }
                if ($this->looksMathContent($text)) {
                    $this->lastImportDiagnostics['validation']['math_preservation']['items_with_math']++;
                }
                if (preg_match('/\\\\[a-zA-Z]+/', $text)) {
                    $this->lastImportDiagnostics['validation']['math_preservation']['items_with_latex']++;
                }
            }
        }
    }

    private function buildNumberingContinuityDiagnostics(array $questions): array
    {
        $numbers = [];
        foreach ($questions as $question) {
            $raw = trim((string) ($question['question_number'] ?? ''));
            if (preg_match('/^\d+$/', $raw)) {
                $numbers[] = (int) $raw;
            }
        }

        $numbers = array_values(array_unique($numbers));
        sort($numbers);

        if (count($numbers) === 0) {
            return [
                'checked' => 0,
                'is_continuous' => true,
                'missing_numbers' => [],
            ];
        }

        $missing = [];
        $set = array_flip($numbers);
        for ($n = $numbers[0]; $n <= $numbers[count($numbers) - 1]; $n++) {
            if (! isset($set[$n])) {
                $missing[] = $n;
            }
        }

        return [
            'checked' => count($numbers),
            'is_continuous' => count($missing) === 0,
            'missing_numbers' => $missing,
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

    public function normalizeCorrectAnswerFields(array $question): array
    {
        $type = strtoupper((string) ($question['type'] ?? 'MSA'));
        $options = array_values($question['options'] ?? []);
        $optionCount = count($options);

        unset($question['answer_validation_message']);
        $question['answer_validation_status'] = 'not_required';

        if (in_array($type, ['MSA', 'TOF'], true)) {
            $resolved = $this->resolveSingleCorrectOptionIndex($question, $options);

            if ($resolved === null) {
                unset($question['correct_option_index']);
                $question['answer_validation_status'] = 'missing';
                $question['answer_validation_message'] = 'Correct answer missing or unclear. Please review before approval.';
            } else {
                $question['correct_option_index'] = $resolved;
                $question['correct_option_indices'] = [];
                $question['correct_option_label'] = chr(65 + $resolved);
                $question['correct_option_text'] = $this->plainOptionText($options[$resolved] ?? '');
                $question['answer_validation_status'] = 'valid';
            }

            return $question;
        }

        if ($type === 'MMA') {
            $indices = $this->resolveMultipleCorrectOptionIndices($question, $options);

            if (count($indices) === 0) {
                unset($question['correct_option_indices']);
                $question['answer_validation_status'] = 'missing';
                $question['answer_validation_message'] = 'Multiple-answer correct options missing or unclear. Please review before approval.';
            } else {
                $question['correct_option_indices'] = $indices;
                unset($question['correct_option_index']);
                $question['answer_validation_status'] = 'valid';
            }

            return $question;
        }

        if (in_array($type, ['FIB', 'SAQ', 'LAQ'], true)) {
            if (! empty($question['correct_answer_text'])) {
                $question['answer_validation_status'] = 'valid';
            } else {
                $question['answer_validation_status'] = 'missing';
                $question['answer_validation_message'] = 'Correct answer text missing or unclear. Please review before approval.';
            }
        }

        return $question;
    }

    private function resolveSingleCorrectOptionIndex(array $question, array $options): ?int
    {
        $optionCount = count($options);
        if ($optionCount === 0) {
            return null;
        }

        if (array_key_exists('correct_option_index', $question) && $question['correct_option_index'] !== null && $question['correct_option_index'] !== '') {
            $idx = (int) $question['correct_option_index'];
            if ($idx >= 0 && $idx < $optionCount) {
                return $idx;
            }
        }

        $candidateFields = [
            'correct_option_label',
            'correct_option_text',
            'correct_answer',
            'answer',
            'correct_answer_text',
        ];

        foreach ($candidateFields as $field) {
            if (! array_key_exists($field, $question)) {
                continue;
            }

            $idx = $this->resolveOptionReference($question[$field], $options);
            if ($idx !== null) {
                return $idx;
            }
        }

        return $this->resolveOptionMarkedCorrect($options, false);
    }

    private function resolveMultipleCorrectOptionIndices(array $question, array $options): array
    {
        $optionCount = count($options);
        if ($optionCount === 0) {
            return [];
        }

        $resolved = [];

        if (isset($question['correct_option_indices']) && is_array($question['correct_option_indices'])) {
            foreach ($question['correct_option_indices'] as $idx) {
                if (is_numeric($idx) && (int) $idx >= 0 && (int) $idx < $optionCount) {
                    $resolved[] = (int) $idx;
                }
            }
        }

        foreach (['correct_answer', 'answer', 'correct_answer_text', 'correct_option_text', 'correct_option_label'] as $field) {
            if (! array_key_exists($field, $question)) {
                continue;
            }

            $values = is_array($question[$field])
                ? $question[$field]
                : preg_split('/\s*,\s*|\s*;\s*/', (string) $question[$field], -1, PREG_SPLIT_NO_EMPTY);

            foreach ($values as $value) {
                $idx = $this->resolveOptionReference($value, $options);
                if ($idx !== null) {
                    $resolved[] = $idx;
                }
            }
        }

        $marked = $this->resolveOptionMarkedCorrect($options, true);
        if (is_array($marked)) {
            $resolved = array_merge($resolved, $marked);
        }

        $resolved = array_values(array_unique(array_filter($resolved, fn ($idx) => $idx >= 0 && $idx < $optionCount)));
        sort($resolved);

        return $resolved;
    }

    private function resolveOptionReference($value, array $options): ?int
    {
        if (is_array($value)) {
            if (array_key_exists('index', $value)) {
                return $this->resolveOptionReference($value['index'], $options);
            }
            if (array_key_exists('option', $value)) {
                return $this->resolveOptionReference($value['option'], $options);
            }
            if (array_key_exists('text', $value)) {
                return $this->resolveOptionReference($value['text'], $options);
            }

            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $optionCount = count($options);

        if (preg_match('/^(?:option\s*)?([A-Z])$/i', $raw, $matches)) {
            $idx = ord(strtoupper($matches[1])) - 65;
            return ($idx >= 0 && $idx < $optionCount) ? $idx : null;
        }

        if (preg_match('/^(?:option\s*)?(\d+)$/i', $raw, $matches)) {
            $number = (int) $matches[1];
            if ($number >= 1 && $number <= $optionCount) {
                return $number - 1;
            }
            if ($number === 0 && $optionCount > 0) {
                return 0;
            }
        }

        $needle = $this->normalizeAnswerText($raw);
        foreach ($options as $idx => $option) {
            if ($needle !== '' && $needle === $this->normalizeAnswerText($this->plainOptionText($option))) {
                return (int) $idx;
            }
        }

        return null;
    }

    private function resolveOptionMarkedCorrect(array $options, bool $multiple)
    {
        $indices = [];
        foreach ($options as $idx => $option) {
            if (is_array($option) && ! empty($option['is_correct'])) {
                $indices[] = (int) $idx;
            }
        }

        if ($multiple) {
            return $indices;
        }

        return count($indices) === 1 ? $indices[0] : null;
    }

    private function plainOptionText($option): string
    {
        if (is_array($option)) {
            $option = $option['option'] ?? $option['text'] ?? '';
        }

        $text = str_replace('[IMAGE HERE]', '', (string) $option);
        return trim(strip_tags($text));
    }

    private function normalizeAnswerText(string $text): string
    {
        $text = strtolower(trim(strip_tags($text)));
        $text = str_replace('[image here]', '', $text);
        $text = preg_replace('/^\(?[a-z]\)?[\.\)\-:]\s*/i', '', $text);
        $text = preg_replace('/^\(?\d+\)?[\.\)\-:]\s*/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
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
        return DB::transaction(fn () => $this->persistReviewedAiQuestions($questionsData, $topicId, $userId));
    }

    private function persistReviewedAiQuestions(array $questionsData, int $topicId, int $userId): int
    {
        $topic = Topic::with('skill')->findOrFail($topicId);
        $skill = $topic->skill ?? Skill::first();
        $defaultDiff = DifficultyLevel::where('code', 'EASY')->first();
        $qTypes = QuestionType::all()->keyBy('code');
        $existingPrefixes = $this->existingQuestionPrefixes($topicId);
        $batchTime = now()->setTimezone('Asia/Kolkata')->format('Ymd_His');
        $insertedCount = 0;

        foreach ($questionsData as $index => $qData) {
            $prepared = $this->prepareAiQuestionForCreate($qData, $index, $topicId, $qTypes);
            if ($prepared === null || in_array($prepared['clean_prefix'], $existingPrefixes, true)) {
                continue;
            }

            Question::create([
                'question_type_id' => $prepared['type']->id,
                'skill_id' => $skill->id,
                'topic_id' => $topic->id,
                'difficulty_level_id' => $defaultDiff->id,
                'question' => $prepared['question']['question'],
                'options' => $prepared['options'],
                'correct_answer' => $prepared['correct_answer'],
                'solution' => $prepared['question']['solution'] ?? '',
                'hint' => $prepared['question']['hint'] ?? '',
                'default_marks' => 1,
                'default_time' => 60,
                'preferences' => $this->repository->setDefaultPreferences($prepared['type_code']),
                'created_by' => $userId,
                'is_active' => true,
                'code' => 'que_ai_'.$batchTime.'_'.$skill->id.'_'.Str::random(5),
            ]);

            $existingPrefixes[] = $prepared['clean_prefix'];
            $insertedCount++;
        }

        $this->logAiImportInsertCount($topicId, $userId, count($questionsData), $insertedCount);

        return $insertedCount;
    }

    private function existingQuestionPrefixes(int $topicId): array
    {
        return Question::where('topic_id', $topicId)
            ->get(['question'])
            ->map(fn ($q) => strtolower(trim(substr(strip_tags($q->question), 0, 100))))
            ->toArray();
    }

    private function prepareAiQuestionForCreate($qData, int $index, int $topicId, $qTypes): ?array
    {
        if (! is_array($qData) || empty($qData['question'])) {
            return null;
        }

        $qData['type'] = $this->resolveCanonicalQuestionType($qData);
        $qData = $this->normalizeCorrectAnswerFields($qData);
        $typeCode = strtoupper((string) ($qData['type'] ?? 'SAQ'));
        $type = $qTypes->get($typeCode) ?: $qTypes->get('SAQ') ?: $qTypes->get('MSA');
        if ($type && isset($type->code)) {
            $typeCode = strtoupper((string) $type->code);
            $qData['type'] = $typeCode;
        }
        $answerPayload = $this->buildAnswerPayload($qData, $typeCode, $index, $topicId);

        if ($answerPayload === null) {
            return null;
        }

        return [
            'question' => $qData,
            'type' => $type,
            'type_code' => $typeCode,
            'options' => $answerPayload['options'],
            'correct_answer' => $answerPayload['correct_answer'],
            'clean_prefix' => strtolower(trim(substr(strip_tags($qData['question']), 0, 100))),
        ];
    }

    private function buildAnswerPayload(array $qData, string $typeCode, int $index, int $topicId): ?array
    {
        return match ($typeCode) {
            'MMA' => $this->buildMultipleAnswerPayload($qData, $index, $topicId),
            'MSA', 'TOF' => $this->buildSingleAnswerPayload($qData, $index, $topicId),
            'MTF' => $this->buildMatchTheFollowingPayload($qData),
            'ORD' => $this->buildOrderingPayload($qData),
            'FIB', 'SAQ' => [
                'options' => [],
                'correct_answer' => $qData['correct_answer_text'] ?? '',
            ],
            'LAQ' => [
                'options' => [],
                'correct_answer' => $qData['correct_answer_text'] ?? '',
            ],
            default => [
                'options' => [],
                'correct_answer' => null,
            ],
        };
    }

    private function buildMultipleAnswerPayload(array $qData, int $index, int $topicId): ?array
    {
        if (($qData['answer_validation_status'] ?? null) !== 'valid') {
            $this->logSkippedAiQuestion('MMA question with missing correct answers', $qData, $index, $topicId);
            return null;
        }

        $options = $this->formatAiOptions($qData['options'] ?? []);
        $indices = is_array($qData['correct_option_indices'] ?? null) ? $qData['correct_option_indices'] : [];
        $correctAnswer = array_map(fn ($i) => (int) $i, $indices);

        foreach ($indices as $idx) {
            if (isset($options[(int) $idx])) {
                $options[(int) $idx]['is_correct'] = true;
            }
        }

        return ['options' => $options, 'correct_answer' => $correctAnswer];
    }

    private function buildSingleAnswerPayload(array $qData, int $index, int $topicId): ?array
    {
        if (($qData['answer_validation_status'] ?? null) !== 'valid' || ! isset($qData['correct_option_index'])) {
            $this->logSkippedAiQuestion('single-answer question with missing correct answer', $qData, $index, $topicId);
            return null;
        }

        $options = $this->formatAiOptions($qData['options'] ?? []);
        $correctIdx = (int) $qData['correct_option_index'];

        if (! isset($options[$correctIdx])) {
            Log::warning('Skipping AI imported single-answer question with out-of-range correct answer', [
                'topic_id' => $topicId,
                'index' => $index,
                'correct_option_index' => $correctIdx,
                'options_count' => count($options),
            ]);
            return null;
        }

        $options[$correctIdx]['is_correct'] = true;

        return ['options' => $options, 'correct_answer' => $correctIdx];
    }

    private function buildMatchTheFollowingPayload(array $qData): array
    {
        $formatted = [];

        foreach (array_values($qData['options'] ?? []) as $option) {
            if (is_array($option)) {
                $left = trim($this->optionHtml($option['option'] ?? $option['text'] ?? ''));
                $pair = trim($this->optionHtml($option['pair'] ?? ''));
            } else {
                $raw = trim((string) $option);
                $left = $raw;
                $pair = '';

                if (preg_match('/^(.*?)\s*(?:\-\>|=>|\|)\s*(.+)$/u', $raw, $matches)) {
                    $left = trim($matches[1]);
                    $pair = trim($matches[2]);
                } elseif (preg_match('/^(.*?)[,:;]\s+(.+)$/u', $raw, $matches)) {
                    $left = trim($matches[1]);
                    $pair = trim($matches[2]);
                }
            }

            if ($left === '') {
                continue;
            }

            $formatted[] = [
                'option' => $left,
                'pair' => $pair,
                'partial_weightage' => 0,
            ];
        }

        return [
            'options' => $formatted,
            'correct_answer' => null,
        ];
    }

    private function buildOrderingPayload(array $qData): array
    {
        return [
            'options' => $this->formatAiOptions($qData['options'] ?? []),
            'correct_answer' => null,
        ];
    }

    private function formatAiOptions(array $options): array
    {
        return array_map(fn ($optText) => [
            'option' => $this->optionHtml($optText),
            'is_correct' => false,
            'partial_weightage' => 0,
        ], $options);
    }

    private function logSkippedAiQuestion(string $reason, array $qData, int $index, int $topicId): void
    {
        Log::warning('Skipping AI imported '.$reason, [
            'topic_id' => $topicId,
            'index' => $index,
            'question_number' => $qData['question_number'] ?? null,
            'source_page' => $qData['source_page'] ?? null,
        ]);
    }

    private function logAiImportInsertCount(int $topicId, int $userId, int $inputCount, int $insertedCount): void
    {
        Log::info('AI import final DB insert count', [
            'topic_id' => $topicId,
            'user_id' => $userId,
            'input_count' => $inputCount,
            'inserted_count' => $insertedCount,
        ]);
    }

    private function optionHtml($option): string
    {
        if (is_array($option)) {
            return (string) ($option['option'] ?? $option['text'] ?? '');
        }

        return (string) $option;
    }

    public function attachImageHtmlToQuestion(array $questions, int $questionIndex, string $imageType, string $imgHtml): array
    {
        if (! isset($questions[$questionIndex]) || ! is_array($questions[$questionIndex])) {
            return $questions;
        }

        if ($imageType === 'question') {
            $questions[$questionIndex]['question'] = $this->appendOrReplaceImagePlaceholder(
                (string) ($questions[$questionIndex]['question'] ?? ''),
                $imgHtml
            );

            return $questions;
        }

        if (! preg_match('/^option_(\d+)$/', $imageType, $matches)) {
            return $questions;
        }

        $optionIndex = (int) $matches[1];
        if (! isset($questions[$questionIndex]['options'][$optionIndex])) {
            return $questions;
        }

        $questions[$questionIndex]['options'][$optionIndex] = $this->appendOrReplaceImagePlaceholder(
            (string) $questions[$questionIndex]['options'][$optionIndex],
            $imgHtml
        );

        return $questions;
    }

    private function appendOrReplaceImagePlaceholder(string $html, string $imgHtml): string
    {
        if (str_contains($html, '[IMAGE HERE]')) {
            return preg_replace('/\[IMAGE HERE\]/', $imgHtml, $html, 1);
        }

        return trim($html) === '' ? $imgHtml : $html . '<br>' . $imgHtml;
    }
}
