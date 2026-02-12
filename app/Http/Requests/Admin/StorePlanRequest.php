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
            'category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:100',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',

            // Logic handled in prepareForValidation
            'has_discount' => 'nullable|boolean',
            'discount_percentage' => 'required|integer|min:0|max:100', // Changed to required because we force a value below

            'description' => 'nullable|string|max:200',
            'sort_order' => 'required|integer|min:0',

            'feature_restrictions' => 'nullable|boolean',
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',

            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        // 1. Checkbox Values Clean Karein
        $hasDiscount = $this->boolean('has_discount') ? 1 : 0;
        
        // $this->boolean() automatically handles "on", "1", "0", null, etc. correctly
        $featureRestrictions = $this->boolean('feature_restrictions') ? 1 : 0;
        
        $this->merge([
            'has_discount' => $hasDiscount,
            'feature_restrictions' => $featureRestrictions,
            'is_popular' => $this->boolean('is_popular') ? 1 : 0,
            'is_active' => $this->boolean('is_active') ? 1 : 0,

            // 2. Discount Percentage Fix (Null hatana)
            // Agar discount checked hai to input value lein, nahi to 0
            'discount_percentage' => $hasDiscount ? ($this->input('discount_percentage') ?? 0) : 0,
        ]);
    }
}