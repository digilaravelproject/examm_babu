<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class ExamUpdateAnswerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'question_id' => ['required', 'exists:questions,id'],
            'section_id'  => ['required', 'exists:exam_sections,id'],
            'user_answer' => ['nullable'],
            'time_taken'  => ['required', 'numeric', 'min:0'],
            'status'      => ['required', 'in:answered,answered_mark_for_review,mark_for_review,skipped,visited'],
        ];
    }
}
