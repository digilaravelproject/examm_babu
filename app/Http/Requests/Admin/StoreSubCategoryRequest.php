<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin middleware already handling this, so set to true
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_type_id' => ['required', 'exists:sub_category_types,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
            // Code nullable kyunki controller auto-generate karega
            'code' => ['nullable', 'string', 'unique:sub_categories,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a parent category.',
            'sub_category_type_id.required' => 'Please select a sub-category type.',
            'name.required' => 'The sub-category name is required.',
        ];
    }
}
