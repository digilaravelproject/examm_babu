<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_type_id' => ['required', 'exists:sub_category_types,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],

            // Name: image_path
            'image_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            'is_active' => ['required', 'boolean'],

            // Ignore current ID for unique code check
            'code' => ['nullable', 'string', Rule::unique('sub_categories', 'code')->ignore($this->sub_category)],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }
}
