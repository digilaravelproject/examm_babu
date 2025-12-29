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
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\DefaultValueBinder;

// --- HELPER CLASS ---
class QuestionsParser extends DefaultValueBinder implements ToArray, WithHeadingRow, WithCustomValueBinder
{
    public function array(array $array) { return $array; }
    public function bindValue(Cell $cell, $value) {
        $cell->setValueExplicit($value, DataType::TYPE_STRING);
        return true;
    }
}

class QuestionImportController_old extends Controller
{
    public function showImportForm() {
        return view('admin.questions.import');
    }

    public function downloadSample() {
        return Excel::download(new QuestionSampleExport, 'question_import_sample.xlsx');
    }

    public function uploadAndPrepare(Request $request) {
        $request->validate(['excel_file' => 'required|mimes:xlsx,csv,xls']);

        try {
            $data = Excel::toArray(new QuestionsParser, $request->file('excel_file'));
            $rows = $data[0] ?? [];

            if (count($rows) === 0) {
                return response()->json(['success' => false, 'message' => 'File is empty.'], 422);
            }

            $rows = array_filter($rows, fn($r) => !empty($r['question']));

            $batchId = Str::random(20);
            $fileName = "import_batch_{$batchId}.json";
            Storage::put('temp/' . $fileName, json_encode(array_values($rows), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return response()->json(['success' => true, 'batch_id' => $batchId, 'total_rows' => count($rows)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function processChunk(Request $request) {
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
            try {
                $this->createQuestion($row);
                $processedCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($offset + $index + 2) . ": " . $e->getMessage();
            }
        }

        $isFinished = ($offset + $limit) >= count($rows);
        if ($isFinished) Storage::delete($fileName);

        return response()->json(['success' => true, 'processed' => $processedCount, 'errors' => $errors, 'finished' => $isFinished]);
    }

    // --- MAIN LOGIC (AUTO-FIX SHIFTED COLUMNS) ---
    private function createQuestion($row) {
        $getVal = fn($key) => isset($row[$key]) ? trim($row[$key]) : null;

        // 1. Question Type
        $type = QuestionType::where('code', $getVal('question_type'))->first();
        $typeId = $type ? $type->id : 1;

        // 2. Skill & Topic
        $skillId = $row['skill_id'] ?? null;
        $topicId = $row['topic_id'] ?? null;

        if(!$skillId || !$topicId) {
             $t = Topic::first();
             $skillId = $skillId ?: ($t ? $t->skill_id : 1);
             $topicId = $topicId ?: ($t ? $t->id : 1);
        }

        // 3. Options
        $options = [];
        for ($i = 1; $i <= 5; $i++) {
            $optText = $getVal('option' . $i);
            if ($optText !== '' && $optText !== null) {
                $options[] = ['option' => (string)$optText, 'image' => null];
            }
        }

        // 4. Correct Answer
        $correctAnswerRaw = $getVal('correct_answer');
        $correctAnswerFinal = null;
        if (str_contains($correctAnswerRaw, ',')) {
            $indicesRaw = explode(',', $correctAnswerRaw);
            $indices = [];
            foreach ($indicesRaw as $val) {
                $idx = (int)trim($val) - 1;
                if (isset($options[$idx])) $indices[] = $idx;
            }
            $correctAnswerFinal = $indices;
        } else {
            if (is_numeric($correctAnswerRaw)) {
                $idx = (int)$correctAnswerRaw - 1;
                if (isset($options[$idx])) $correctAnswerFinal = $idx;
            }
            if ($correctAnswerFinal === null) {
                 foreach($options as $k => $opt) {
                     if(trim(strtolower($opt['option'])) == trim(strtolower($correctAnswerRaw))) {
                         $correctAnswerFinal = $k; break;
                     }
                 }
            }
        }

        // 5. Solution
        $solution = $getVal('solution');

        // --- SMART COLUMN FIX ---
        // Problem: 'default_marks' column me text aa raha hai ('Solution text here.')
        // Solution: Check karo ki number hai ya nahi.

        $rawMarks = $row['default_marks'] ?? '1';
        $rawTime = $row['default_time_to_solve'] ?? '60';
        $rawDiff = $row['difficulty_level'] ?? 'EASY';
        $rawHint = $row['hint'] ?? null;

        $finalMarks = 1;
        $finalTime = 60;
        $finalDiffName = 'EASY';
        $finalHint = null;

        if (!is_numeric($rawMarks)) {
            // ERROR DETECTED: Column Shifted hai!
            // 'default_marks' me text hai, iska matlab ye extra Hint hai.
            $finalHint = $rawMarks; // Text ko hint bana do

            // Asli marks 'time' wale column me shift ho gaye hain
            $finalMarks = is_numeric($rawTime) ? $rawTime : 1;

            // Asli time 'difficulty' wale column me shift ho gaya hai
            $finalTime = is_numeric($rawDiff) ? $rawDiff : 60;

            // Asli difficulty 'hint' wale column me shift ho gayi hai
            $finalDiffName = $rawHint ?? 'EASY';
        } else {
            // Sab sahi hai
            $finalMarks = $rawMarks;
            $finalTime = $rawTime;
            $finalDiffName = $rawDiff;
            $finalHint = $rawHint;
        }

        // Difficulty Lookup
        $diffLevel = DifficultyLevel::where('name', trim($finalDiffName))->orWhere('code', trim($finalDiffName))->first();
        $diffId = $diffLevel ? $diffLevel->id : 1;

        Question::create([
            'question_type_id'    => $typeId,
            'skill_id'            => $skillId,
            'topic_id'            => $topicId,
            'difficulty_level_id' => $diffId,
            'question'            => (string)$getVal('question'),
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
