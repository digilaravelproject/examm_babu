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

            // Discount Logic
            'has_discount' => 'nullable|boolean',
            'discount_percentage' => 'nullable|required_if:has_discount,1|numeric|max:100',

            // Description & Sorting
            'description' => 'nullable|string|max:200',
            'sort_order' => 'required|integer|min:0',

            // Feature Logic (Validation zaroori hai)
            'feature_restrictions' => 'nullable|boolean',
            'features' => 'nullable|array', // ✅ Ye allow karein
            'features.*' => 'exists:features,id', // Check karein ki features valid hain

            // Toggles
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    // Checkboxes ko 1/0 me convert karne ke liye
    protected function prepareForValidation()
    {
        $this->merge([
            'has_discount' => $this->has('has_discount') ? 1 : 0,
            'feature_restrictions' => $this->has('feature_restrictions') ? 1 : 0,
            'is_popular' => $this->has('is_popular') ? 1 : 0,
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }
}
