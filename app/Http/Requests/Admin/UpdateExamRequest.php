<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true; // Role check controller mein middleware se handle ho raha hai
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Route se current exam ID nikalna (unique validation ke liye useful agar code update ho raha ho)
        $examId = $this->route('exam') instanceof \App\Models\Exam
            ? $this->route('exam')->id
            : $this->route('exam');

        return [
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'exam_type_id'    => ['required', 'exists:exam_types,id'],
            'exam_mode'       => ['required', 'string', 'in:online,offline'],
            'sub_category_id' => ['required', 'exists:sub_categories,id'],

            // Boolean handling for Laravel 12 / Blade
            'is_paid'         => ['required', 'boolean'],
            'can_redeem'      => ['required', 'boolean'],

            // Logic: Agar redeem enabled hai toh points must hain
            'points_required' => [
                'required_if:can_redeem,1',
                'required_if:can_redeem,true',
                'nullable',
                'numeric',
                'min:0'
            ],

            'is_private'      => ['required', 'boolean'],
            'is_active'       => ['required', 'boolean'],

            // Agar aap exam code update allow karte hain:
            'code'            => ['sometimes', 'string', 'unique:exams,code,' . $examId],
        ];
    }

    /**
     * Data cleanup before validation
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_paid'    => $this->boolean('is_paid'),
            'can_redeem' => $this->boolean('can_redeem'),
            'is_private' => $this->boolean('is_private'),
            'is_active'  => $this->boolean('is_active'),
        ]);
    }

    /**
     * Custom Error Messages
     */
    public function messages(): array
    {
        return [
            'points_required.required_if' => 'Redeem points are mandatory when redemption is enabled.',
            'exam_type_id.exists'         => 'The selected exam type is no longer available.',
        ];
    }
}
