<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:micro_categories,id',
            'name' => 'required|string|max:100',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'has_discount' => 'nullable|boolean',
            'discount_percentage' => 'required|integer|min:0|max:100',
            'description' => 'nullable|string|max:200',
            'sort_order' => 'required|integer|min:0',
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $hasDiscount = $this->boolean('has_discount') ? 1 : 0;

        $this->merge([
            'has_discount' => $hasDiscount,
            'is_popular' => $this->boolean('is_popular') ? 1 : 0,
            'is_active' => $this->boolean('is_active') ? 1 : 0,
            'discount_percentage' => $hasDiscount ? ($this->input('discount_percentage') ?? 0) : 0,
        ]);
    }
}
