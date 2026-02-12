<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
