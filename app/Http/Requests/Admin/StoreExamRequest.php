<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Yahan aap check kar sakte hain agar user admin/instructor hai
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'exam_type_id'    => ['required', 'exists:exam_types,id'],
            'exam_mode'       => ['required', 'string', 'in:online,offline'],
            'sub_category_id' => ['required', 'exists:sub_categories,id'],

            // Boolean fields (Blade checkbox/radio ke liye better handling)
            'is_paid'         => ['required', 'boolean'],
            'can_redeem'      => ['required', 'boolean'],

            // Logic: Agar can_redeem true hai toh points required hain aur minimum 1 hona chahiye
            'points_required' => [
                'required_if:can_redeem,1',
                'required_if:can_redeem,true',
                'nullable',
                'numeric',
                'min:0'
            ],

            'is_private'      => ['required', 'boolean'],
            'is_active'       => ['required', 'boolean'],

            // Optional: Slug ya Code agar custom bhej rahe ho
            'code'            => ['nullable', 'string', 'unique:exams,code'],
        ];
    }

    /**
     * Custom messages for better UX
     */
    public function messages(): array
    {
        return [
            'points_required.required_if' => 'Please specify the points required to redeem this exam.',
            'exam_type_id.exists'         => 'The selected exam type is invalid.',
            'sub_category_id.exists'      => 'The selected category is invalid.',
        ];
    }

    /**
     * Handle data before validation (Optional)
     */
    protected function prepareForValidation()
    {
        $this->merge([
            // Laravel Blade forms aksar checkbox na hone par null bhejte hain,
            // isliye hum unhe boolean mein convert kar sakte hain
            'is_paid'    => $this->boolean('is_paid'),
            'can_redeem' => $this->boolean('can_redeem'),
            'is_private' => $this->boolean('is_private'),
            'is_active'  => $this->boolean('is_active'),
        ]);
    }
}
