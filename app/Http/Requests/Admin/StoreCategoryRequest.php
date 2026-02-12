<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'unique:categories,code'],
            'short_description' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            // YAHAN CHANGE KIYA: 'image' ki jagah 'image_path' kar diya
            'image_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }
}
