<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamSectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'section_id' => ['required', 'exists:sections,id'],
            'section_order' => ['required', 'integer'],
            'auto_duration' => ['required', 'boolean'],
            'total_duration' => ['required_if:auto_duration,false', 'nullable', 'numeric'],
            'auto_grading' => ['required', 'boolean'],
            'correct_marks' => ['required_if:auto_grading,false', 'nullable', 'numeric'],
            'enable_section_cutoff' => ['required', 'boolean'],
            'section_cutoff' => ['required_if:enable_section_cutoff,true', 'nullable', 'numeric'],
            'enable_negative_marking' => ['required', 'boolean'],
            'negative_marking_type' => ['required_if:enable_negative_marking,true', 'nullable', 'string'],
            'negative_marks' => ['required_if:enable_negative_marking,true', 'nullable', 'numeric'],
        ];
    }
}
