<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\DifficultyLevel;
use App\Models\Skill;
use App\Models\Topic;
use App\Exports\QuestionSampleExport;
use App\Repositories\QuestionRepository; // Repository Import
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuestionImportController extends Controller
{
    private QuestionRepository $repository;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function showImportForm()
    {
        return view('admin.questions.import');
    }

    public function downloadSample()
    {
        return Excel::download(new QuestionSampleExport, 'question_import_sample.xlsx');
    }

    public function uploadAndPrepare(Request $request)
    {
        set_time_limit(0);
        $request->validate(['excel_file' => 'required|mimes:xlsx,csv,xls']);

        try {
            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
            $rows = [];

            if ($extension === 'csv') {
                $rows = $this->readCsvNative($path);
            } else {
                $rows = $this->readExcelDirect($path);
            }

            if (empty($rows)) {
                return response()->json(['success' => false, 'message' => 'File is empty or headers could not be read.'], 422);
            }

            $batchId = Str::random(20);
            $fileName = "import_batch_{$batchId}.json";
            Storage::put('temp/' . $fileName, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'total_rows' => count($rows)
            ]);
        } catch (\Exception $e) {
            Log::error("Import Upload Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Critical Error: ' . $e->getMessage()], 500);
        }
    }

    public function processChunk(Request $request)
    {
        $batchId = $request->batch_id;
        $offset = $request->offset;
        $limit = $request->limit;
        $fileName = "temp/import_batch_{$batchId}.json";

        if (!Storage::exists($fileName)) {
            return response()->json(['success' => false, 'message' => 'Batch file lost.'], 404);
        }

        $rows = json_decode(Storage::get($fileName), true);
        $chunk = array_slice($rows, $offset, $limit);
        $processedCount = 0;
        $errors = [];

        foreach ($chunk as $index => $row) {
            $rowNumber = $offset + $index + 2;
            DB::beginTransaction();
            try {
                $this->createQuestion($row);
                DB::commit();
                $processedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        $isFinished = ($offset + $limit) >= count($rows);
        if ($isFinished) {
            Storage::delete($fileName);
        }

        return response()->json([
            'success' => true,
            'processed' => $processedCount,
            'errors' => $errors,
            'finished' => $isFinished
        ]);
    }

    // ... CSV/Excel Readers (Same as your code) ...
    private function readCsvNative($path)
    {
        $rows = [];
        $header = null;
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if (!$header) {
                    $data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
                    $header = $this->normalizeKeys($data);
                } else {
                    if (count($data) < count($header)) $data = array_pad($data, count($header), null);
                    $row = array_combine($header, array_slice($data, 0, count($header)));
                    if ($this->hasData($row)) $rows[] = $row;
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    private function readExcelDirect($path)
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = [];
        $header = [];
        $isFirstRow = true;

        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) $rowData[] = (string)$cell->getFormattedValue();

            if ($isFirstRow) {
                $header = $this->normalizeKeys($rowData);
                $isFirstRow = false;
            } else {
                if (count($rowData) >= count($header)) {
                    $mappedRow = [];
                    foreach ($header as $index => $key) $mappedRow[$key] = $rowData[$index] ?? null;
                    if ($this->hasData($mappedRow)) $rows[] = $mappedRow;
                }
            }
        }
        return $rows;
    }

    private function normalizeKeys($row)
    {
        return array_map(function ($key) {
            return str_replace([' ', '_', '-'], '', strtolower(trim($key)));
        }, $row);
    }

    private function hasData($row)
    {
        return !empty(trim($row['question'] ?? ''));
    }

    // =========================================================================
    //  MAIN LOGIC: CREATE QUESTION (UPDATED TO MATCH REPOSITORY STRUCTURE)
    // =========================================================================

    private function createQuestion($row)
    {
        $get = fn($key) => isset($row[$key]) ? trim($row[$key]) : null;

        // 1. Text & Type
        $questionText = $get('question');
        if (empty($questionText)) throw new \Exception("Question text missing.");

        $typeCode = strtoupper($get('questiontype') ?? $get('type') ?? 'MSA');
        $type = QuestionType::where('code', $typeCode)->first();
        if (!$type) throw new \Exception("Invalid Question Type: $typeCode");
        $typeId = $type->id;

        // 2. Skill & Topic
        $skillId = $get('skillid') ?? $get('skill');
        $topicId = $get('topicid') ?? $get('topic');

        if ($skillId && !Skill::find($skillId)) $skillId = null;
        if ($topicId && !Topic::find($topicId)) $topicId = null;

        if (!$skillId || !$topicId) {
            $defaultTopic = Topic::first();
            if (!$defaultTopic) throw new \Exception("No Topics found.");
            $skillId = $skillId ?: $defaultTopic->skill_id;
            $topicId = $topicId ?: $defaultTopic->id;
        }

        // 3. Options Parsing (Matching Old Structure)
        $options = [];
        $rawOptions = [];

        // MTF Special Parsing (Option,Pair)
        if ($typeCode === 'MTF') {
            for ($i = 1; $i <= 5; $i++) {
                $optVal = $get('option' . $i);
                $pairVal = $get('pair' . $i) ?? $get('option' . $i . 'pair'); // Flexible key check

                if ($optVal !== '' && $optVal !== null) {
                    $options[] = [
                        'option' => $optVal,
                        'pair' => $pairVal ?? '',
                        'partial_weightage' => 0
                    ];
                }
            }
        }
        // Standard Options
        else {
            for ($i = 1; $i <= 6; $i++) { // Extended to 6 just in case
                $val = $get('option' . $i);
                if ($val !== '' && $val !== null) {
                    $options[] = [
                        'option' => $val,
                        'partial_weightage' => 0 // Consistent with Repo
                    ];
                    $rawOptions[] = $val; // For Answer Matching
                }
            }
        }

        // 4. Correct Answer Logic
        $correctAnswerRaw = $get('correctanswer') ?? $get('answer');
        $correctAnswerFinal = null;

        if ($typeCode === 'FIB') {
            // FIB: Extract from text ##..##
            if (function_exists('getBlankItems')) {
                $correctAnswerFinal = getBlankItems($questionText);
            } else {
                preg_match_all('/##(.*?)##/', $questionText, $matches);
                $correctAnswerFinal = $matches[1] ?? [];
            }
            // FIB Options are generated dynamically, but we keep structure empty
            $options = [];
        } elseif ($typeCode === 'MTF' || $typeCode === 'ORD' || $typeCode === 'SAQ') {
            $correctAnswerFinal = null; // Stored inside options or calculated
        } else {
            // MSA, MMA, TOF
            if (empty($correctAnswerRaw)) throw new \Exception("Correct Answer missing.");

            if (str_contains($correctAnswerRaw, ',')) {
                // Multiple Answers (Indices)
                $indicesRaw = explode(',', $correctAnswerRaw);
                $indices = [];
                foreach ($indicesRaw as $val) {
                    $idx = (int)trim($val) - 1;
                    if (isset($rawOptions[$idx])) $indices[] = $idx;
                }
                $correctAnswerFinal = $indices;
            } else {
                // Single Answer
                if (is_numeric($correctAnswerRaw)) {
                    $idx = (int)$correctAnswerRaw - 1;
                    if (isset($rawOptions[$idx])) $correctAnswerFinal = $idx;
                } else {
                    // Text Match
                    foreach ($options as $k => $opt) {
                        if (strcasecmp(trim($opt['option']), trim($correctAnswerRaw)) === 0) {
                            $correctAnswerFinal = $k;
                            break;
                        }
                    }
                }
            }
        }

        // 5. Preferences (Using Repository Logic)
        $preferences = $this->repository->setDefaultPreferences($typeCode);

        // 6. Meta Data
        $rawMarks = $get('defaultmarks') ?? $get('marks') ?? '1';
        $rawTime = $get('defaulttimetosolve') ?? $get('time') ?? '60';
        $rawDiff = $get('difficultylevel') ?? $get('difficulty') ?? 'EASY';

        $diffLevel = DifficultyLevel::where('name', trim($rawDiff))->orWhere('code', trim($rawDiff))->first();
        $diffId = $diffLevel ? $diffLevel->id : 1;

        // 7. Store
        Question::create([
            'question_type_id'    => $typeId,
            'skill_id'            => $skillId,
            'topic_id'            => $topicId,
            'difficulty_level_id' => $diffId,
            'question'            => $questionText,
            'options'             => $options,
            'correct_answer'      => $correctAnswerFinal,
            'solution'            => $get('solution'),
            'default_marks'       => is_numeric($rawMarks) ? $rawMarks : 1,
            'default_time'        => is_numeric($rawTime) ? $rawTime : 60,
            'hint'                => $get('hint'),
            'preferences'         => $preferences, // Ensure shuffle/limits are set
            'created_by'          => Auth::id() ?? 1,
            'is_active'           => true,
            'code'                => 'IMP_' . Str::random(8),
        ]);
    }
}
