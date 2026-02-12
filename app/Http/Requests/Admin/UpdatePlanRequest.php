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
            'category_id' => 'required|integer|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'duration' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',

            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',

            'has_discount' => 'nullable|boolean',
            'discount_percentage' => 'required|integer|min:0|max:100', // Validation enforce karega ki value ho
            
            'feature_restrictions' => 'nullable|boolean',
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',
        ];
    }

    protected function prepareForValidation()
    {
        // 1. Checkbox Values Clean Karein
        $hasDiscount = $this->boolean('has_discount') ? 1 : 0;
        $featureRestrictions = $this->boolean('feature_restrictions') ? 1 : 0;

        $this->merge([
            'is_active' => $this->boolean('is_active') ? 1 : 0,
            'is_popular' => $this->boolean('is_popular') ? 1 : 0,
            'has_discount' => $hasDiscount,
            'feature_restrictions' => $featureRestrictions,

            // 2. Discount Fix
            'discount_percentage' => $hasDiscount ? ($this->input('discount_percentage') ?? 0) : 0,
        ]);
    }
}