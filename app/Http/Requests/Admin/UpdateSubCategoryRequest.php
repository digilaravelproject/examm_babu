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
        // Get the sub-category ID from the route
        $subCategoryId = $this->route('sub_category')->id ?? $this->route('sub_category');

        return [
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_type_id' => ['required', 'exists:sub_category_types,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
            'code' => [
                'nullable',
                'string',
                Rule::unique('sub_categories', 'code')->ignore($subCategoryId)
            ],
        ];
    }
}
