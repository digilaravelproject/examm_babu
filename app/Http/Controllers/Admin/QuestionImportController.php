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
use Maatwebsite\Excel\Facades\Excel; // Export ke liye
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class QuestionImportController extends Controller
{
    public function showImportForm() {
        return view('admin.questions.import');
    }

    public function downloadSample() {
        return Excel::download(new QuestionSampleExport, 'question_import_sample.xlsx');
    }

    /**
     * STEP 1: UPLOAD & PARSE FILE
     * Reads Excel/CSV directly to avoid format conversion issues.
     */
    public function uploadAndPrepare(Request $request) {
        // Increase time limit for large uploads
        set_time_limit(0);
        $request->validate(['excel_file' => 'required|mimes:xlsx,csv,xls']);

        try {
            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
            $rows = [];

            // Choose Strategy based on file type
            if ($extension === 'csv') {
                $rows = $this->readCsvNative($path);
            } else {
                $rows = $this->readExcelDirect($path);
            }

            if (empty($rows)) {
                return response()->json(['success' => false, 'message' => 'File is empty or headers could not be read.'], 422);
            }

            // Store in Temp JSON for Queue Processing
            $batchId = Str::random(20);
            $fileName = "import_batch_{$batchId}.json";

            // JSON Encode with robust flags (Handles Hindi/Math symbols safely)
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

    /**
     * STEP 2: PROCESS QUEUE CHUNK
     * Processes questions in small batches to prevent server timeout.
     */
    public function processChunk(Request $request) {
        $batchId = $request->batch_id;
        $offset = $request->offset;
        $limit = $request->limit;
        $fileName = "temp/import_batch_{$batchId}.json";

        if (!Storage::exists($fileName)) {
            return response()->json(['success' => false, 'message' => 'Batch processing file lost. Please upload again.'], 404);
        }

        // Read Data
        $rows = json_decode(Storage::get($fileName), true);

        // Slice Chunk
        $chunk = array_slice($rows, $offset, $limit);

        $processedCount = 0;
        $errors = [];

        foreach ($chunk as $index => $row) {
            $rowNumber = $offset + $index + 2; // +2 because Excel starts at 1 and has Header

            DB::beginTransaction(); // Start Transaction
            try {
                $this->createQuestion($row);
                DB::commit(); // Save if successful
                $processedCount++;
            } catch (\Exception $e) {
                DB::rollBack(); // Undo if failed
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        // Check if finished
        $isFinished = ($offset + $limit) >= count($rows);
        if ($isFinished) {
            Storage::delete($fileName); // Cleanup
        }

        return response()->json([
            'success' => true,
            'processed' => $processedCount,
            'errors' => $errors,
            'finished' => $isFinished
        ]);
    }

    // =========================================================================
    //  HELPER: READERS (Native & Direct)
    // =========================================================================

    /**
     * Native CSV Reader (Fastest & Accurate for Text)
     */
    private function readCsvNative($path) {
        $rows = [];
        $header = null;

        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                // Remove BOM (Byte Order Mark) from first cell if present
                if (!$header) {
                    $data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
                    $header = $this->normalizeKeys($data);
                } else {
                    // Safety: Pad row if columns are missing
                    if (count($data) < count($header)) {
                        $data = array_pad($data, count($header), null);
                    }

                    // Map Header -> Value
                    $row = array_combine($header, array_slice($data, 0, count($header)));

                    // Filter: Only add if 'question' exists
                    if ($this->hasData($row)) {
                        $rows[] = $row;
                    }
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Direct PhpSpreadsheet Reader (Best for Excel 10% formatting)
     */
    private function readExcelDirect($path) {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(false); // We need formatting!
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $rows = [];
        $header = [];
        $isFirstRow = true;

        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                // THE GOLDEN KEY: Get what the user sees
                $val = $cell->getFormattedValue();
                $rowData[] = (string)$val;
            }

            if ($isFirstRow) {
                $header = $this->normalizeKeys($rowData);
                $isFirstRow = false;
            } else {
                 if (count($rowData) >= count($header)) {
                    $mappedRow = [];
                    foreach($header as $index => $key) {
                        $mappedRow[$key] = $rowData[$index] ?? null;
                    }

                    if ($this->hasData($mappedRow)) {
                        $rows[] = $mappedRow;
                    }
                 }
            }
        }
        return $rows;
    }

    // Helper: Normalize Headers (option 1 -> option1)
    private function normalizeKeys($row) {
        return array_map(function($key) {
            // Remove space, underscore, dash, make lowercase
            return str_replace([' ', '_', '-'], '', strtolower(trim($key)));
        }, $row);
    }

    // Helper: Check if row has valid question data
    private function hasData($row) {
        return !empty(trim($row['question'] ?? ''));
    }

    // =========================================================================
    //  MAIN LOGIC: CREATE QUESTION
    // =========================================================================

    private function createQuestion($row) {
        // Safe Value Getter
        $get = fn($key) => isset($row[$key]) ? trim($row[$key]) : null;

        // 1. Validate Question Text
        $questionText = $get('question');
        if (empty($questionText)) {
            throw new \Exception("Question text is missing.");
        }

        // 2. Question Type
        $typeCode = $get('questiontype') ?? $get('type') ?? 'MSA';
        $type = QuestionType::where('code', $typeCode)->first();
        $typeId = $type ? $type->id : 1;

        // 3. Skill & Topic (Smart DB Lookup)
        $skillId = $get('skillid') ?? $get('skill');
        $topicId = $get('topicid') ?? $get('topic');

        // Logic: Agar ID di hai to verify karo, nahi to default first ID lo
        if ($skillId && !Skill::find($skillId)) $skillId = null;
        if ($topicId && !Topic::find($topicId)) $topicId = null;

        if(!$skillId || !$topicId) {
             $defaultTopic = Topic::first();
             if (!$defaultTopic) throw new \Exception("No Topics found in database. Please add topics first.");

             $skillId = $skillId ?: $defaultTopic->skill_id;
             $topicId = $topicId ?: $defaultTopic->id;
        }

        // 4. Options Processing
        $options = [];
        for ($i = 1; $i <= 5; $i++) {
            $val = $get('option' . $i);
            // Ignore empty options
            if ($val !== '' && $val !== null) {
                $options[] = ['option' => $val, 'image' => null];
            }
        }

        if (empty($options)) {
            throw new \Exception("No options provided for question.");
        }

        // 5. Correct Answer Logic
        $correctAnswerRaw = $get('correctanswer') ?? $get('answer');
        $correctAnswerFinal = null;

        if (empty($correctAnswerRaw)) {
            // Warn but don't crash, maybe set to 0? Or throw error.
            // Let's throw error to be safe.
            throw new \Exception("Correct Answer is missing.");
        }

        // Multiple Answers (comma separated)
        if (str_contains($correctAnswerRaw, ',')) {
            $indicesRaw = explode(',', $correctAnswerRaw);
            $indices = [];
            foreach ($indicesRaw as $val) {
                $idx = (int)trim($val) - 1; // 1-based to 0-based
                if (isset($options[$idx])) $indices[] = $idx;
            }
            $correctAnswerFinal = $indices;
        } else {
            // Single Answer
            if (is_numeric($correctAnswerRaw)) {
                $idx = (int)$correctAnswerRaw - 1;
                if (isset($options[$idx])) $correctAnswerFinal = $idx;
            }

            // Text Match Fallback
            if ($correctAnswerFinal === null) {
                 foreach($options as $k => $opt) {
                     // Strict comparison for accuracy
                     if(strcasecmp(trim($opt['option']), trim($correctAnswerRaw)) === 0) {
                         $correctAnswerFinal = $k;
                         break;
                     }
                 }
            }
        }

        // 6. Solution
        $solution = $get('solution');

        // 7. Column Shift Logic (Smart Detection)
        $rawMarks = $get('defaultmarks') ?? $get('marks') ?? '1';
        $rawTime = $get('defaulttimetosolve') ?? $get('time') ?? '60';
        $rawDiff = $get('difficultylevel') ?? $get('difficulty') ?? 'EASY';
        $rawHint = $get('hint');

        $finalMarks = 1; $finalTime = 60; $finalDiffName = 'EASY'; $finalHint = null;

        // Agar marks numeric nahi hai, iska matlab column shift hua hai (data idhar udhar hai)
        if (!is_numeric($rawMarks)) {
            $finalHint = $rawMarks; // Text found in marks column is actually Hint
            $finalMarks = is_numeric($rawTime) ? $rawTime : 1;
            $finalTime = is_numeric($rawDiff) ? $rawDiff : 60;
            $finalDiffName = $rawHint ?? 'EASY';
        } else {
            $finalMarks = $rawMarks;
            $finalTime = $rawTime;
            $finalDiffName = $rawDiff;
            $finalHint = $rawHint;
        }

        $diffLevel = DifficultyLevel::where('name', trim($finalDiffName))->orWhere('code', trim($finalDiffName))->first();
        $diffId = $diffLevel ? $diffLevel->id : 1;

        // 8. Final Insert
        Question::create([
            'question_type_id'    => $typeId,
            'skill_id'            => $skillId,
            'topic_id'            => $topicId,
            'difficulty_level_id' => $diffId,
            'question'            => $questionText,
            'options'             => $options,
            'correct_answer'      => $correctAnswerFinal,
            'solution'            => $solution,
            'default_marks'       => $finalMarks,
            'default_time'        => $finalTime,
            'hint'                => $finalHint,
            'created_by'          => Auth::id() ?? 1,
            'is_active'           => true,
            'code'                => 'que_' . Str::random(10),
        ]);
    }
}
