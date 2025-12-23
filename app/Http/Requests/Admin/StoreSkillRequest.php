<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    public function authorize() { return true; }

 public function rules()
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'short_description' => ['nullable', 'string', 'max:255'],
        'section_id' => ['required', 'exists:sections,id'],
        'is_active' => ['required', 'boolean'],
    ];
}
}
