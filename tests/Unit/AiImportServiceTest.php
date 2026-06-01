<?php

use App\Services\AiImportService;
use App\Http\Controllers\Admin\AiImportController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;

function aiImportServiceWithoutConstructor(): AiImportService
{
    Log::swap(new class {
        public function __call($name, $arguments)
        {
            //
        }
    });

    $service = (new ReflectionClass(AiImportService::class))->newInstanceWithoutConstructor();
    $property = new ReflectionProperty(AiImportService::class, 'lastImportDiagnostics');
    $property->setAccessible(true);
    $property->setValue($service, ['deduped' => []]);

    return $service;
}

function callAiImportPrivate(AiImportService $service, string $method, array $args = [])
{
    $reflection = new ReflectionMethod(AiImportService::class, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($service, $args);
}

test('normalizes page fields and preserves 75 plus question payloads', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [];

    for ($i = 1; $i <= 75; $i++) {
        $questions[] = [
            'type' => 'MSA',
            'question_number' => (string) $i,
            'question' => "Question {$i}",
            'page_number' => 2 + intdiv($i - 1, 10),
            'options' => ['A', 'B', 'C', 'D'],
        ];
    }

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 2, 10);

    expect($normalized)->toHaveCount(75)
        ->and($normalized[0]['source_page'])->toBe(2)
        ->and($normalized[0]['page_number_extracted'])->toBe(2);
});

test('normalizes 100 plus question payloads without dropping similar text blindly', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [];

    for ($i = 1; $i <= 100; $i++) {
        $questions[] = [
            'type' => 'MSA',
            'question_number' => (string) $i,
            'question' => 'Select the odd term.',
            'source_page' => 5 + intdiv($i - 1, 20),
            'options' => ['A', 'B', 'C', 'D'],
        ];
    }

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 5, 9);

    expect($normalized)->toHaveCount(100);
});

test('normalizes question and option image boxes including legacy formats', function () {
    $service = aiImportServiceWithoutConstructor();

    $questions = [[
        'type' => 'MSA',
        'question' => 'Question with [IMAGE HERE]',
        'page_number_extracted' => 3,
        'image_box' => ['100', '200', '300', '400'],
        'option_image_boxes' => [
            [300, 100, 400, 200],
            ['index' => 2, 'box' => [410.4, 110.2, 500.8, 230.1]],
            ['index' => 3, 'box' => [900, 100, 800, 200]],
        ],
    ]];

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 3, 3);

    expect($normalized[0]['image_box'])->toBe([94, 194, 306, 406])
        ->and($normalized[0]['option_image_boxes'])->toBe([
            '0' => [294, 94, 406, 206],
            '2' => [404, 104, 507, 236],
        ]);
});

test('rejects malformed coordinates', function () {
    $service = aiImportServiceWithoutConstructor();

    expect($service->normalizeImageBox([100, 200, 50, 400]))->toBeNull()
        ->and($service->normalizeImageBox([100, -1, 200, 400]))->toBeNull()
        ->and($service->normalizeImageBox([100, 200, 200]))->toBeNull()
        ->and($service->normalizeImageBox([100, 200, 300, 400]))->toBe([94, 194, 306, 406]);
});

test('keeps english and math text while filtering multilingual duplicates', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [[
        'type' => 'MSA',
        'question' => "What is acceleration due to gravity?\nगुरुत्वीय त्वरण का मान क्या है?\n\\frac{a}{b} + \\sqrt{x}",
        'source_page' => 1,
        'options' => [
            "9.8 m/s^2\n९.८ मी/से^2",
            '10 m/s^2',
            '\\frac{1}{2}x^2',
            'None of these',
        ],
    ]];

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 1, 1);

    expect($normalized)->toHaveCount(1)
        ->and($normalized[0]['question'])->toContain('What is acceleration due to gravity?')
        ->and($normalized[0]['question'])->not->toContain('गुरुत्वीय')
        ->and($normalized[0]['question'])->toContain('\\frac{a}{b} + \\sqrt{x}')
        ->and($normalized[0]['options'][0])->toBe('9.8 m/s^2')
        ->and($normalized[0]['options'][2])->toBe('\\frac{1}{2}x^2');
});

test('drops uncertain mixed language question blocks and tracks diagnostics', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [[
        'type' => 'MSA',
        'question' => 'What is गुरुत्व acceleration?',
        'source_page' => 1,
        'options' => ['A', 'B', 'C', 'D'],
    ]];

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 1, 1);
    $diagnostics = $service->getLastImportDiagnostics();

    expect($normalized)->toBe([])
        ->and($diagnostics['validation']['english_filter']['questions_dropped'])->toBe(1)
        ->and($diagnostics['validation']['english_filter']['mixed_blocks_detected'])->toBeGreaterThan(0);
});

test('canonicalizes extended question types from type text and question content', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [
        [
            'type' => 'Match the Following',
            'question' => 'Match the following:',
            'options' => ['A -> 1', 'B -> 2'],
            'source_page' => 1,
        ],
        [
            'type' => 'ordering',
            'question' => 'Arrange the following in correct sequence.',
            'options' => ['One', 'Two', 'Three'],
            'source_page' => 1,
        ],
        [
            'type' => 'custom',
            'question' => 'Assertion (A): ... Reason (R): ...',
            'options' => ['A', 'B', 'C', 'D'],
            'source_page' => 1,
        ],
        [
            'type' => 'subjective long answer',
            'question' => 'Explain in detail.',
            'options' => [],
            'source_page' => 1,
        ],
        [
            'type' => 'numerical',
            'question' => 'Compute the value.',
            'options' => [],
            'source_page' => 1,
        ],
    ];

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 1, 1);

    expect($normalized[0]['type'])->toBe('MTF')
        ->and($normalized[1]['type'])->toBe('ORD')
        ->and($normalized[2]['type'])->toBe('MSA')
        ->and($normalized[3]['type'])->toBe('LAQ')
        ->and($normalized[4]['type'])->toBe('SAQ');
});

test('final validation diagnostics include numbering type english image and math checks', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [
        [
            'type' => 'MSA',
            'question_number' => '1',
            'question' => 'Q1 with math \\frac{a}{b}',
            'options' => ['A', 'B', 'C', 'D'],
            'image_box' => [100, 100, 200, 200],
            'source_page' => 1,
        ],
        [
            'type' => 'MSA',
            'question_number' => '3',
            'question' => 'Q3',
            'options' => ['A', 'B', 'C', 'D'],
            'option_image_boxes' => [['index' => 0, 'box' => [300, 200, 380, 280]]],
            'source_page' => 1,
        ],
    ];

    $normalized = $service->normalizeExtractedQuestionsForImport($questions, 1, 1);
    callAiImportPrivate($service, 'finalizeValidationDiagnostics', [$normalized]);
    $diagnostics = $service->getLastImportDiagnostics();

    expect($diagnostics)->toHaveKey('validation')
        ->and($diagnostics['validation']['total_extracted_count'])->toBe(2)
        ->and($diagnostics['validation']['numbering']['checked'])->toBe(2)
        ->and($diagnostics['validation']['numbering']['is_continuous'])->toBeFalse()
        ->and($diagnostics['validation']['numbering']['missing_numbers'])->toBe([2])
        ->and($diagnostics['validation']['type_distribution']['MSA'])->toBe(2)
        ->and($diagnostics['validation']['english_filter']['questions_retained'])->toBe(2)
        ->and($diagnostics['validation']['image_mapping']['questions_with_image'])->toBe(1)
        ->and($diagnostics['validation']['image_mapping']['options_with_image'])->toBe(1)
        ->and($diagnostics['validation']['math_preservation']['items_with_latex'])->toBeGreaterThan(0);
});

test('repairs truncated gemini json and reports it as repaired', function () {
    $service = aiImportServiceWithoutConstructor();

    $parsed = callAiImportPrivate($service, 'parseAiResponseWithMeta', [
        '[{"type":"MSA","question":"Q1","options":["A","B"]}',
    ]);

    expect($parsed['questions'])->toHaveCount(1)
        ->and($parsed['repaired'])->toBeTrue();
});

test('safe dedupe requires page number question number and normalized text', function () {
    $service = aiImportServiceWithoutConstructor();

    $questions = [
        ['source_page' => 1, 'question_number' => '1', 'question' => 'Same text'],
        ['source_page' => 1, 'question_number' => '1', 'question' => 'Same text'],
        ['source_page' => 1, 'question_number' => '2', 'question' => 'Same text'],
        ['source_page' => 2, 'question_number' => '1', 'question' => 'Same text'],
        ['source_page' => 2, 'question_number' => '', 'question' => 'Same text'],
        ['source_page' => 2, 'question_number' => '', 'question' => 'Same text'],
    ];

    $deduped = callAiImportPrivate($service, 'dedupeExtractedQuestions', [$questions, 'test-batch']);

    expect($deduped)->toHaveCount(5);
});

test('verified public image url uses current request root to avoid stale app url 404s', function () {
    $app = new Application(getcwd());
    Container::setInstance($app);
    Facade::setFacadeApplication($app);
    $app->instance('request', Request::create('http://xampp.test/admin/ai-import/upload-cropped-image', 'POST'));

    Log::swap(new class {
        public function __call($name, $arguments)
        {
            //
        }
    });

    $controller = (new ReflectionClass(AiImportController::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(AiImportController::class, 'verifiedPublicStorageUrl');
    $method->setAccessible(true);

    $url = $method->invoke($controller, 'ai_extracted/batch/img_1.jpg');

    expect($url)->toBe('http://xampp.test/storage/ai_extracted/batch/img_1.jpg');
});

test('maps single correct answers from label number text and legacy fields', function () {
    $service = aiImportServiceWithoutConstructor();

    $byLabel = $service->normalizeCorrectAnswerFields([
        'type' => 'MSA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
        'correct_option_label' => 'C',
    ]);

    $byOneBasedNumber = $service->normalizeCorrectAnswerFields([
        'type' => 'MSA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
        'correct_answer' => '2',
    ]);

    $byText = $service->normalizeCorrectAnswerFields([
        'type' => 'MSA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
        'correct_answer_text' => 'Gamma',
    ]);

    expect($byLabel['correct_option_index'])->toBe(2)
        ->and($byOneBasedNumber['correct_option_index'])->toBe(1)
        ->and($byText['correct_option_index'])->toBe(2)
        ->and($byText['answer_validation_status'])->toBe('valid');
});

test('flags missing single correct answer instead of guessing option one', function () {
    $service = aiImportServiceWithoutConstructor();

    $normalized = $service->normalizeCorrectAnswerFields([
        'type' => 'MSA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
    ]);

    expect($normalized['answer_validation_status'])->toBe('missing')
        ->and($normalized)->not->toHaveKey('correct_option_index');
});

test('maps multiple correct answers and legacy is correct option flags', function () {
    $service = aiImportServiceWithoutConstructor();

    $fromLabels = $service->normalizeCorrectAnswerFields([
        'type' => 'MMA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
        'correct_answer' => 'A, D',
    ]);

    $fromFlags = $service->normalizeCorrectAnswerFields([
        'type' => 'MMA',
        'question' => 'Q',
        'options' => [
            ['option' => 'Alpha', 'is_correct' => true],
            ['option' => 'Beta', 'is_correct' => false],
            ['option' => 'Gamma', 'is_correct' => true],
        ],
    ]);

    expect($fromLabels['correct_option_indices'])->toBe([0, 3])
        ->and($fromFlags['correct_option_indices'])->toBe([0, 2]);
});

test('ai mcq correct answer normalization uses same zero based indices as normal import flow', function () {
    $service = aiImportServiceWithoutConstructor();

    $single = $service->normalizeCorrectAnswerFields([
        'type' => 'MSA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
        'correct_answer' => 'B',
    ]);

    $multiple = $service->normalizeCorrectAnswerFields([
        'type' => 'MMA',
        'question' => 'Q',
        'options' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
        'correct_answer' => 'B, D',
    ]);

    expect($single['correct_option_index'])->toBe(1)
        ->and($multiple['correct_option_indices'])->toBe([1, 3]);
});

test('question image is attached only to question field', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [[
        'question' => 'Question [IMAGE HERE]',
        'options' => ['A', 'B [IMAGE HERE]'],
    ]];

    $updated = $service->attachImageHtmlToQuestion($questions, 0, 'question', '<img src="/q.jpg" />');

    expect($updated[0]['question'])->toContain('/q.jpg')
        ->and($updated[0]['options'][0])->toBe('A')
        ->and($updated[0]['options'][1])->toBe('B [IMAGE HERE]');
});

test('option image is attached only to matching option field', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [[
        'question' => 'Question [IMAGE HERE]',
        'options' => ['A [IMAGE HERE]', 'B [IMAGE HERE]'],
    ]];

    $updated = $service->attachImageHtmlToQuestion($questions, 0, 'option_1', '<img src="/b.jpg" />');

    expect($updated[0]['question'])->toBe('Question [IMAGE HERE]')
        ->and($updated[0]['options'][0])->toBe('A [IMAGE HERE]')
        ->and($updated[0]['options'][1])->toContain('/b.jpg');
});

test('invalid option image target does not fall back to question field', function () {
    $service = aiImportServiceWithoutConstructor();
    $questions = [[
        'question' => 'Question',
        'options' => ['A', 'B'],
    ]];

    $updated = $service->attachImageHtmlToQuestion($questions, 0, 'option_9', '<img src="/wrong.jpg" />');

    expect($updated)->toBe($questions);
});
