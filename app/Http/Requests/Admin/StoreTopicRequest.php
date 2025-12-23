<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'short_description' => ['nullable', 'string', 'max:255'],
        'skill_id' => ['required', 'exists:skills,id'],
        'is_active' => ['required', 'boolean'],
    ];
}


}
