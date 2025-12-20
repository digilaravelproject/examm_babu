<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionType;
use App\Models\DifficultyLevel;
use App\Models\Skill;
use App\Models\Topic;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class QuestionsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        if (!isset($row['question']) || empty($row['question'])) {
            return null;
        }

        // 1. Question Type
        $type = QuestionType::where('code', trim($row['question_type']))->first();
        $typeId = $type ? $type->id : 1;

        // 2. Difficulty
        $diffName = trim($row['difficulty_level'] ?? 'EASY');
        $diffLevel = DifficultyLevel::where('name', $diffName)->orWhere('code', $diffName)->first();
        $diffId = $diffLevel ? $diffLevel->id : 1;

        // 3. SKILL & TOPIC (Manual IDs from Excel)
        $skillId = $row['skill_id'];
        $topicId = $row['topic_id'];

        // Validation for IDs (Optional but recommended)
        if (!Skill::find($skillId)) {
             // You can throw exception or fallback. Throwing exception is safer for data integrity.
             throw new \Exception("Invalid Skill ID: {$skillId} for Question: '{$row['question']}'.");
        }
        if (!Topic::find($topicId)) {
             throw new \Exception("Invalid Topic ID: {$topicId} for Question: '{$row['question']}'.");
        }

        // 4. Options Construction (FIXED FORMAT)
        // Expected: [{"option":"Text", "image":null}, ...]
        $options = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($row['option' . $i]) && trim($row['option' . $i]) !== '') {
                $options[] = [
                    'option' => trim($row['option' . $i]),
                    'image'  => null // Default null as requested
                ];
            }
        }

        // 5. Correct Answer Logic (FIXED: Store Index, not Text)
        // Excel contains 1-based index (e.g., 2 for 2nd option).
        // DB expects 0-based index integer (e.g., 1).

        $correctAnswerRaw = trim($row['correct_answer']);
        $correctAnswerFinal = null;

        if (str_contains($correctAnswerRaw, ',')) {
            // Multiple Answers (MMA) -> Store Array of Integers
            $indicesRaw = explode(',', $correctAnswerRaw);
            $indices = [];
            foreach ($indicesRaw as $val) {
                $idx = (int)trim($val) - 1; // Convert 1-based to 0-based
                if (isset($options[$idx])) {
                    $indices[] = $idx;
                }
            }
            $correctAnswerFinal = $indices;
        } else {
            // Single Answer (MSA) -> Store Single Integer
            if (is_numeric($correctAnswerRaw)) {
                $idx = (int)$correctAnswerRaw - 1; // Convert 1-based to 0-based
                if (isset($options[$idx])) {
                    $correctAnswerFinal = $idx;
                }
            } else {
                // Fallback: If for some reason text is provided (unlikely if strictly following template)
                // Try to find index by text match
                foreach ($options as $key => $optObj) {
                    if (strcasecmp($optObj['option'], $correctAnswerRaw) === 0) {
                        $correctAnswerFinal = $key;
                        break;
                    }
                }
            }
        }

        return new Question([
            'question_type_id'    => $typeId,
            'skill_id'            => $skillId,
            'topic_id'            => $topicId,
            'difficulty_level_id' => $diffId,
            'question'            => $row['question'],
            'options'             => $options, // Array of objects automatically cast to JSON
            'correct_answer'      => $correctAnswerFinal, // Integer or Array, automatically serialized by Model
            'default_marks'       => $row['default_marks'] ?? 1,
            'default_time'        => $row['default_time_to_solve'] ?? 60,
            'hint'                => $row['hint'] ?? null,
            'solution'            => $row['solution'] ?? null,
            'created_by'          => $this->userId,
            'is_active'           => true,
            'code'                => 'que_' . Str::random(10),
        ]);
    }

    public function rules(): array
    {
        return [
            'question' => 'required',
            'skill_id' => 'required|integer',
            'topic_id' => 'required|integer',
            'question_type' => 'required',
            'correct_answer' => 'required',
        ];
    }
}
