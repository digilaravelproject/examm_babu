<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Attempt Restrictions
            'restrict_attempts'           => ['required', 'boolean'],
            'no_of_attempts'              => ['required_if:restrict_attempts,true', 'required_if:restrict_attempts,1', 'nullable', 'integer', 'min:1'],

            // Navigation Settings
            'disable_question_navigation' => ['required', 'boolean'],
            'disable_section_navigation'  => ['required', 'boolean'],
            'list_questions'              => ['required', 'boolean'],
            'disable_finish_button'       => ['required', 'boolean'],

            // Exam Logic
            'auto_duration'               => ['required', 'boolean'],
            'auto_grading'                => ['required', 'boolean'],
            'shuffle_questions'           => ['required', 'boolean'],

            // Marking & Cutoffs
            'enable_negative_marking'     => ['required', 'boolean'],
            'enable_section_cutoff'       => ['required', 'boolean'],
            'cutoff'                      => ['required', 'numeric', 'min:0', 'max:100'],

            // Visibility
            'hide_solutions'              => ['required', 'boolean'],
            'show_leaderboard'            => ['required', 'boolean'],
        ];
    }

    /**
     * Prepare data for validation.
     * Blade checkboxes agar check na ho toh request mein nahi aate,
     * isliye hum unhe default 'false' merge kar dete hain.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'restrict_attempts'           => $this->boolean('restrict_attempts'),
            'disable_question_navigation' => $this->boolean('disable_question_navigation'),
            'disable_section_navigation'  => $this->boolean('disable_section_navigation'),
            'list_questions'              => $this->boolean('list_questions'),
            'auto_duration'               => $this->boolean('auto_duration'),
            'auto_grading'                => $this->boolean('auto_grading'),
            'enable_negative_marking'     => $this->boolean('enable_negative_marking'),
            'enable_section_cutoff'       => $this->boolean('enable_section_cutoff'),
            'disable_finish_button'       => $this->boolean('disable_finish_button'),
            'hide_solutions'              => $this->boolean('hide_solutions'),
            'shuffle_questions'           => $this->boolean('shuffle_questions'),
            'show_leaderboard'            => $this->boolean('show_leaderboard'),
        ]);
    }

    /**
     * Custom Attribute Names for Error Messages
     */
    public function attributes(): array
    {
        return [
            'no_of_attempts' => 'Number of Attempts',
            'cutoff'         => 'Passing Cutoff Percentage',
        ];
    }
}
