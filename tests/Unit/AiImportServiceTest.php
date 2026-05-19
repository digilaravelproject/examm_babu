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

    expect($normalized[0]['image_box'])->toBe([100, 200, 300, 400])
        ->and($normalized[0]['option_image_boxes'])->toBe([
            '0' => [300, 100, 400, 200],
            '2' => [410, 110, 501, 230],
        ]);
});

test('rejects malformed coordinates', function () {
    $service = aiImportServiceWithoutConstructor();

    expect($service->normalizeImageBox([100, 200, 50, 400]))->toBeNull()
        ->and($service->normalizeImageBox([100, -1, 200, 400]))->toBeNull()
        ->and($service->normalizeImageBox([100, 200, 200]))->toBeNull()
        ->and($service->normalizeImageBox([100, 200, 300, 400]))->toBe([100, 200, 300, 400]);
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
