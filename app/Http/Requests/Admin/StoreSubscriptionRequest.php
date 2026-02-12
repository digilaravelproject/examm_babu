<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'status'  => 'required|in:active,created,cancelled,expired',
            'starts_at' => 'nullable|date', // Optional, defaults to Now
        ];
    }
}
