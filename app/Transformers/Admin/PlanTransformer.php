<?php

namespace App\Transformers\Admin;

use App\Models\Plan;
use App\Settings\PaymentSettings;
use League\Fractal\TransformerAbstract;

class PlanTransformer extends TransformerAbstract
{
    /**
     * Use Constructor Injection for settings
     */
    public function __construct(
        protected PaymentSettings $paymentSettings = new PaymentSettings()
    ) {}

    /**
     * A Fractal transformer.
     *
     * @param Plan $plan
     * @return array<string, mixed>
     */
    public function transform(Plan $plan): array
    {
        return [
            'id'       => $plan->id,
            'name'     => $plan->name,
            'category' => $plan->category?->name ?? __('N/A'), // Null-safe operator
            'price'    => formatPrice(
                $plan->price, 
                $this->paymentSettings->currency_symbol, 
                $this->paymentSettings->currency_symbol_position
            ),
            'code'     => $plan->code,
            'duration' => "{$plan->duration} " . __('Months'), // Template literal style
            'status'   => (bool) $plan->is_active, // Cast to boolean for JS/Frontend consistency
        ];
    }
}