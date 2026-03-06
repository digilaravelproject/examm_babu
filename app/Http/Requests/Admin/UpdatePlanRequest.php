<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:micro_categories,id',
            'name' => 'required|string|max:255',
            'duration' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',

            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',

            'has_discount' => 'nullable|boolean',
            'discount_percentage' => 'required|integer|min:0|max:100',
        ];
    }

    protected function prepareForValidation()
    {
        $hasDiscount = $this->boolean('has_discount') ? 1 : 0;
        $this->merge([
            'is_active' => $this->boolean('is_active') ? 1 : 0,
            'is_popular' => $this->boolean('is_popular') ? 1 : 0,
            'has_discount' => $hasDiscount,
            'discount_percentage' => $hasDiscount ? ($this->input('discount_percentage') ?? 0) : 0,
        ]);
    }
}
