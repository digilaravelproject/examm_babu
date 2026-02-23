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
use App\Repositories\QuestionRepository;
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
        // Dropdown ke liye Topics bhejna zaroori hai
        // Eager load relationships to prevent N+1 and get hierarchy data
        $topics = Topic::with(['skill.microCategory.subCategory'])
            ->orderBy('name')
            ->get();
        return view('admin.questions.import', compact('topics'));
    }

    public function downloadSample()
    {
        return Excel::download(new QuestionSampleExport, 'question_import_sample.xlsx');
    }

    // --- STEP 1: Upload File, Validate & Convert to JSON ---
    public function uploadAndPrepare(Request $request)
    {
        set_time_limit(0);

        // Validation: Topic ID is mandatory from Dropdown
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,csv,xls',
            'topic_id'   => 'required|exists:topics,id'
        ]);

        try {
            // 1. Get Selected Topic Details from DB
            $selectedTopicId = (int) $request->topic_id;
            $selectedTopic = Topic::find($selectedTopicId);
            $selectedTopicName = $selectedTopic->name;

            // 2. Read File
            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
            $rows = [];

            if ($extension === 'csv') {
                $rows = $this->readCsvNative($path);
            } else {
                $rows = $this->readExcelDirect($path);
            }

            // Remove empty rows
            $rows = array_filter($rows, function ($row) {
                return !empty($row['question']);
            });

            if (empty($rows)) {
                return response()->json(['success' => false, 'message' => 'File is empty or no valid questions found.'], 422);
            }

            // --- STRICT VALIDATION LOGIC START ---
            foreach ($rows as $index => &$row) {
                // Sheet se Topic value nikalo (topicid, topic_id, ya topic)
                $sheetTopicVal = $row['topicid'] ?? $row['topic_id'] ?? $row['topic'] ?? null;
                $sheetTopicVal = trim((string)$sheetTopicVal);

                // Agar Sheet me Topic likha hai, to match karo
                if (!empty($sheetTopicVal)) {
                    $isMismatch = false;

                    if (is_numeric($sheetTopicVal)) {
                        // Agar ID hai (e.g. "2"), to Selected ID se match karo
                        if ((int)$sheetTopicVal !== $selectedTopicId) {
                            $isMismatch = true;
                        }
                    } else {
                        // Agar Name hai (e.g. "Science"), to Selected Name se match karo
                        if (strtolower($sheetTopicVal) !== strtolower($selectedTopicName)) {
                            $isMismatch = true;
                        }
                    }

                    // Agar Mismatch hua to ERROR return karo
                    if ($isMismatch) {
                        return response()->json([
                            'success' => false,
                            'message' => "Mismatch at Row " . ($index + 2) . ": Sheet has Topic '{$sheetTopicVal}', but you selected '{$selectedTopicName}' (ID: {$selectedTopicId}). Please select the correct topic or fix the sheet."
                        ], 422);
                    }
                }

                // Agar match hua ya sheet me topic empty hai, to Selected Topic inject karo
                $row['final_topic_id'] = $selectedTopicId;
            }
            // --- STRICT VALIDATION LOGIC END ---

            // Save to temp storage for chunking
            $batchId = Str::random(20);
            $fileName = "import_batch_{$batchId}.json";
            Storage::put('temp/' . $fileName, json_encode(array_values($rows), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

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

    // --- STEP 2: Process Small Batches (AJAX Loop) ---
    public function processChunk(Request $request)
    {
        $batchId = $request->batch_id;
        $offset = $request->offset;
        $limit = $request->limit;
        $fileName = "temp/import_batch_{$batchId}.json";

        if (!Storage::exists($fileName)) {
            return response()->json(['success' => false, 'message' => 'Batch file not found or expired.'], 404);
        }

        $rows = json_decode(Storage::get($fileName), true);
        $chunk = array_slice($rows, $offset, $limit);
        $processedCount = 0;
        $errors = [];

        foreach ($chunk as $index => $row) {
            $rowNumber = $offset + $index + 2;
            DB::beginTransaction();
            try {
                // $this->createQuestion($row);
                $this->createQuestion($row, $rowNumber);
                DB::commit();
                $processedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        $isFinished = ($offset + $limit) >= count($rows);

        // Cleanup on finish
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

    // =========================================================================
    //  DATA PARSING HELPERS
    // =========================================================================

    private function readCsvNative($path)
    {
        $rows = [];
        $header = null;
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
                }

                if (!$header) {
                    $header = $this->normalizeKeys($data);
                } else {
                    if (count($data) < count($header)) {
                        $data = array_pad($data, count($header), null);
                    }
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
        $reader->setReadDataOnly(true);
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
                $rowData[] = (string)$cell->getFormattedValue();
            }

            if ($isFirstRow) {
                $header = $this->normalizeKeys($rowData);
                $isFirstRow = false;
            } else {
                if (count($rowData) >= count($header)) {
                    $mappedRow = [];
                    foreach ($header as $index => $key) {
                        $mappedRow[$key] = $rowData[$index] ?? null;
                    }
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
    //  CORE LOGIC: CREATE QUESTION FROM ROW
    // =========================================================================

    // private function createQuestion($row)
    private function createQuestion($row, $rowNum)
    {
        $get = fn($key) => isset($row[$key]) ? trim($row[$key]) : null;

        $questionText = $get('question');
        if (empty($questionText)) throw new \Exception("Question text is empty.");

        // 2. Identify Question Type
        $typeCode = strtoupper($get('questiontype') ?? $get('type') ?? 'MSA');
        $type = QuestionType::where('code', $typeCode)->first();
        if (!$type) throw new \Exception("Invalid Question Type code: $typeCode");
        $typeId = $type->id;

        // 3. Resolve Topic & Skill (Use Injected ID)
        if (isset($row['final_topic_id'])) {
            $topic = Topic::findOrFail($row['final_topic_id']);
            $skill = $topic->skill;
        } else {
            // Fallback (Should not verify if validation works)
            $topic = Topic::first();
            $skill = $topic->skill;
        }

        // 4. Resolve Difficulty
        $diffInput = $get('difficultylevel') ?? $get('difficulty');
        if (empty($diffInput)) {
            throw new \Exception("Difficulty Level is mandatory.");
        }
        $solutionText = $get('solution');
        if (empty($solutionText)) {
            throw new \Exception("Solution field is mandatory.");
        }
        $diffLevel = DifficultyLevel::where('name', $diffInput)->orWhere('code', $diffInput)->first();
        $diffId = $diffLevel ? $diffLevel->id : 1;

        // 5. Parse Options based on Type
        $options = [];
        $rawOptions = [];
        if ($typeCode === 'MSA' || $typeCode === 'MMA') {
            // Option 1, 2, 3, 4 mandatory check
            if (empty($get('option1')) || empty($get('option2')) || empty($get('option3')) || empty($get('option4'))) {
                throw new \Exception("For $typeCode questions, Option 1 to Option 4 are mandatory.");
            }
        }

        if ($typeCode === 'MTF') {
            for ($i = 1; $i <= 5; $i++) {
                $left = $get('option' . $i);
                $right = $get('pair' . $i) ?? $get('option' . $i . 'pair');

                if (!empty($left)) {
                    $options[] = [
                        'option' => $left,
                        'pair' => $right ?? '',
                        'partial_weightage' => 0
                    ];
                }
            }
        } else {
            for ($i = 1; $i <= 6; $i++) {
                $val = $get('option' . $i);
                if ($val !== '' && $val !== null) {
                    $options[] = [
                        'option' => $val,
                        'image' => null,
                        'is_correct' => false,
                        'partial_weightage' => 0
                    ];
                    $rawOptions[] = strtolower(trim($val));
                }
            }
        }

        // 6. Determine Correct Answer
        $correctAnswerRaw = $get('correctanswer') ?? $get('answer');
        $correctAnswerFinal = null;

        if ($typeCode === 'FIB') {
            preg_match_all('/##(.*?)##/', $questionText, $matches);
            $correctAnswerFinal = isset($matches[1]) ? $matches[1] : [];
            $options = [];
        } elseif ($typeCode === 'MTF' || $typeCode === 'ORD' || $typeCode === 'SAQ') {
            $correctAnswerFinal = null;

            // SAQ Fallback: if options empty but answer is provided in sheet
            if ($typeCode === 'SAQ' && empty($options) && !empty($correctAnswerRaw)) {
                $options[] = [
                    'option' => $correctAnswerRaw,
                    'image' => null,
                    'is_correct' => false,
                    'partial_weightage' => 0
                ];
            }
        } else {
            if (empty($correctAnswerRaw)) throw new \Exception("Correct Answer is missing.");

            if (str_contains($correctAnswerRaw, ',')) {
                $indices = [];
                $parts = explode(',', $correctAnswerRaw);
                foreach ($parts as $part) {
                    $indices[] = $this->resolveOptionIndex($part, $rawOptions);
                }
                $correctAnswerFinal = array_filter($indices, fn($x) => $x !== null);

                if ($typeCode === 'MMA') {
                    foreach ($correctAnswerFinal as $idx) {
                        if (isset($options[$idx])) $options[$idx]['is_correct'] = true;
                    }
                    // $correctAnswerFinal remains as array of indices
                }
            } else {
                $idx = $this->resolveOptionIndex($correctAnswerRaw, $rawOptions);
                if ($idx !== null) {
                    $correctAnswerFinal = $idx;
                    if (isset($options[$idx])) $options[$idx]['is_correct'] = true;
                } else {
                    throw new \Exception("Correct answer '$correctAnswerRaw' does not match any provided options.");
                }
            }
        }

        // 7. Get Default Preferences
        $preferences = $this->repository->setDefaultPreferences($typeCode);

        // 8. Create the Record
        $question = Question::create([
            'question_type_id'    => $typeId,
            'skill_id'            => $skill->id,
            'topic_id'            => $topic->id, // This is now strictly validated
            'difficulty_level_id' => $diffId,
            'question'            => $questionText,
            'options'             => $options,
            'correct_answer'      => $correctAnswerFinal,
            'solution'            => $solutionText,
            'hint'                => $get('hint'),
            'default_marks'       => is_numeric($get('marks')) ? $get('marks') : 1,
            'default_time'        => is_numeric($get('time')) ? $get('time') : 60,
            'preferences'         => $preferences,
            'created_by'          => Auth::id() ?? 1,
            'is_active'           => true,
        ]);

        // $question->code = 'que_' . now()->format('Ymd') . '_' . $question->id;
        $question->code = 'que_' . now()->setTimezone('Asia/Kolkata')->format('Ymd_His') . '_' . $skill->id . '_' . $rowNum;
        $question->save();
    }

    private function resolveOptionIndex($input, $rawOptions)
    {
        $input = trim($input);
        if (is_numeric($input)) {
            $idx = (int)$input - 1;
            if (isset($rawOptions[$idx])) return $idx;
        }
        if (strlen($input) === 1 && ctype_alpha($input)) {
            $idx = ord(strtoupper($input)) - 65;
            if (isset($rawOptions[$idx])) return $idx;
        }
        $inputLower = strtolower($input);
        $found = array_search($inputLower, $rawOptions);
        return ($found !== false) ? $found : null;
    }
}
