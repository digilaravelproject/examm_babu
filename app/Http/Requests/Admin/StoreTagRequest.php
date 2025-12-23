<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Agar update ho raha hai toh current ID ko unique check se ignore karein
        $tagId = $this->route('tag') ? $this->route('tag')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($tagId)
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tag ka naam likhna zaroori hai.',
            'name.unique' => 'Ye tag pehle se maujood hai.',
        ];
    }
}
