<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuestionService
{
    /**
     * Create Question Logic
     */
    public function createQuestion(array $data, Request $request): Question
    {
        return DB::transaction(function () use ($data, $request) {

            // 1. Prepare Basic Data (Skill, Topic, etc.)
            $questionData = $this->prepareBasicData($data);
            $questionData['created_by'] = Auth::id();
            $questionData['is_active'] = Auth::user()->hasRole('admin') ? true : false;

            // 2. Handle Main Question Image (Native Input)
            if ($request->hasFile('question_image')) {
                $path = $request->file('question_image')->store('questions', 'public');
                // Append image HTML to question text logic
                $questionData['question'] .= '<br><img src="/storage/' . $path . '" class="img-fluid rounded mt-2" alt="Question Image">';
            }

            // 3. Process Options (Mix Text & Files)
            $questionData['options'] = $this->processOptions($request);

            // 4. Create
            return Question::create($questionData);
        });
    }

    /**
     * Update Question Logic
     */
    public function updateQuestion(Question $question, array $data, Request $request): Question
    {
        return DB::transaction(function () use ($question, $data, $request) {

            // 1. Prepare Data
            $updateData = $this->prepareBasicData($data);

            // Reset approval if instructor edits
            if (!Auth::user()->hasRole('admin')) {
                $updateData['is_active'] = false;
            }

            // 2. Handle Main Question Image
            if ($request->hasFile('question_image')) {
                $path = $request->file('question_image')->store('questions', 'public');
                $updateData['question'] = $data['question'] . '<br><img src="/storage/' . $path . '" class="img-fluid rounded mt-2" alt="Question Image">';
            }

            // 3. Process Options
            $updateData['options'] = $this->processOptions($request);

            // 4. Update
            $question->update($updateData);

            return $question;
        });
    }

    /**
     * Helper: Clean Data & Cast Types
     */
    private function prepareBasicData(array $data): array
    {
        return [
            // Settings Fields
            'question_type_id'    => $data['question_type_id'] ?? null,
            'skill_id'            => $data['skill_id'] ?? null,
            'topic_id'            => $data['topic_id'] ?? null,
            'difficulty_level_id' => $data['difficulty_level_id'] ?? null,
            'default_marks'       => $data['default_marks'] ?? 1,
            'default_time'        => $data['default_time'] ?? 60,

            // Content Fields
            'question'            => $data['question'] ?? '',
            'solution'            => $data['solution'] ?? null,
            'solution_video'      => $data['solution_video'] ?? null,
            'hint'                => $data['hint'] ?? null,

            // Attachments
            'has_attachment'         => $data['has_attachment'] ?? 0,
            'attachment_type'        => $data['attachment_type'] ?? null,
            'comprehension_passage_id' => ($data['attachment_type'] ?? '') == 'comprehension' ? ($data['comprehension_id'] ?? null) : null,
            'attachment_options'     => isset($data['attachment_options']) ? $data['attachment_options'] : null,

            // FIX: Force Integer for Correct Answer (Fixes s:1:"2" issue)
            'correct_answer'      => isset($data['correct_answer']) ? (int) $data['correct_answer'] : null,
        ];
    }

    /**
     * Helper: Merge Option Text with Images
     */
    private function processOptions(Request $request): array
    {
        $textOptions = $request->input('options', []); // From Input Fields
        $fileOptions = $request->file('options', []);  // From File Uploads

        $finalOptions = [];

        if (is_array($textOptions)) {
            foreach ($textOptions as $index => $optData) {

                // 1. Check for Existing Image (Passed from hidden input)
                $imagePath = $optData['existing_image'] ?? null;

                // 2. Check for New Image Upload (Overrides existing)
                if (isset($fileOptions[$index]['image'])) {
                    $file = $fileOptions[$index]['image'];
                    $path = $file->store('options', 'public');
                    $imagePath = '/storage/' . $path;
                }

                $finalOptions[] = [
                    'option' => $optData['option'] ?? null,
                    'image'  => $imagePath,
                    // Keep structure consistent
                    'is_correct' => false
                ];
            }
        }

        return $finalOptions;
    }
}
