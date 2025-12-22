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

// Helper Class to Parse Excel to Array
class QuestionsParser implements ToArray, WithHeadingRow {
    public function array(array $array) { return $array; }
}

class QuestionImportController extends Controller
{
    public function showImportForm() {
        return view('admin.questions.import');
    }

    public function downloadSample() {
        return Excel::download(new QuestionSampleExport, 'question_import_sample.xlsx');
    }

    // STEP 1: Upload File & Convert to JSON (Returns Total Count)
    public function uploadAndPrepare(Request $request) {
        $request->validate(['excel_file' => 'required|mimes:xlsx,csv,xls']);

        try {
            // 1. Parse Excel to Array
            $data = Excel::toArray(new QuestionsParser, $request->file('excel_file'));

            // Excel can have multiple sheets, take the first one with data
            $rows = $data[0] ?? [];

            if (count($rows) === 0) {
                return response()->json(['success' => false, 'message' => 'File is empty or headers are missing.'], 422);
            }

            // 2. Filter empty rows
            $rows = array_filter($rows, fn($r) => !empty($r['question']));

            // 3. Save as Temporary JSON file
            $batchId = Str::random(20);
            $fileName = "import_batch_{$batchId}.json";
            Storage::put('temp/' . $fileName, json_encode(array_values($rows)));

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'total_rows' => count($rows)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // STEP 2: Process a Chunk (Called repeatedly by JS)
    public function processChunk(Request $request) {
        $batchId = $request->batch_id;
        $offset = $request->offset;
        $limit = $request->limit;

        $fileName = "temp/import_batch_{$batchId}.json";

        if (!Storage::exists($fileName)) {
            return response()->json(['success' => false, 'message' => 'Batch file not found. Reload page.'], 404);
        }

        // Read JSON
        $rows = json_decode(Storage::get($fileName), true);

        // Get Chunk
        $chunk = array_slice($rows, $offset, $limit);
        $processedCount = 0;

        foreach ($chunk as $row) {
            try {
                $this->createQuestion($row);
                $processedCount++;
            } catch (\Exception $e) {
                // Skip failed row or log it
                // Log::error("Row failed: " . json_encode($row) . " Error: " . $e->getMessage());
            }
        }

        // Cleanup if finished
        $isFinished = ($offset + $limit) >= count($rows);
        if ($isFinished) {
            Storage::delete($fileName);
        }

        return response()->json(['success' => true, 'processed' => $processedCount, 'finished' => $isFinished]);
    }

    // --- HELPER: Main Logic to Create Question (Moved from Import Class) ---
    private function createQuestion($row) {
        // 1. Types & IDs
        $type = QuestionType::where('code', trim($row['question_type']))->first();
        $typeId = $type ? $type->id : 1;

        $diffName = trim($row['difficulty_level'] ?? 'EASY');
        $diffLevel = DifficultyLevel::where('name', $diffName)->orWhere('code', $diffName)->first();
        $diffId = $diffLevel ? $diffLevel->id : 1;

        // 2. Skill & Topic (Smart Manual + Fallback)
        $skillId = $row['skill_id'] ?? null;
        $topicId = $row['topic_id'] ?? null;

        // Agar CSV me ID nahi hai, to DB se pehla uthao (Fallback)
        if(!$skillId || !$topicId) {
             $t = Topic::first();
             $skillId = $skillId ?: ($t ? $t->skill_id : 1);
             $topicId = $topicId ?: ($t ? $t->id : 1);
        }

        // 3. Options Formatting
        $options = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($row['option' . $i]) && trim($row['option' . $i]) !== '') {
                $options[] = ['option' => trim($row['option' . $i]), 'image' => null];
            }
        }

        // 4. Correct Answer (Index Logic)
        $correctAnswerRaw = trim($row['correct_answer']);
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
            } else {
                 // Text matching fallback
                 foreach($options as $k => $opt) {
                     if(strtolower($opt['option']) == strtolower($correctAnswerRaw)) {
                         $correctAnswerFinal = $k; break;
                     }
                 }
            }
        }

        Question::create([
            'question_type_id'    => $typeId,
            'skill_id'            => $skillId,
            'topic_id'            => $topicId,
            'difficulty_level_id' => $diffId,
            'question'            => $row['question'],
            'options'             => $options, // Casts to JSON automatically
            'correct_answer'      => $correctAnswerFinal, // Serializes automatically
            'default_marks'       => $row['default_marks'] ?? 1,
            'default_time'        => $row['default_time_to_solve'] ?? 60,
            'hint'                => $row['hint'] ?? null,
            'solution'            => $row['solution'] ?? null,
            'created_by'          => Auth::id(),
            'is_active'           => true,
            'code'                => 'que_' . Str::random(10),
        ]);
    }
}
