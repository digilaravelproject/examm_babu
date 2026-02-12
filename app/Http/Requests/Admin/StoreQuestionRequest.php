<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question'         => ['required', 'string'],
            'question_type_id' => ['required', 'exists:question_types,id'],
            'skill_id'         => ['required', 'exists:skills,id'],
            'options'          => ['nullable', 'array'],
            'correct_answer'   => ['nullable'],
            'preferences'      => ['nullable', 'array'],
        ];
    }
}
