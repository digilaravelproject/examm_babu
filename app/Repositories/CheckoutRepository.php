<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Settings\PaymentSettings;
use App\Settings\TaxSettings;

class CheckoutRepository
{
    public function __construct(
        protected PaymentSettings $paymentSettings,
        protected TaxSettings $taxSettings
    ) {}

    public function orderSummary($plan): array
    {
        // 1. Base Calculations
        $originalPrice = $plan->total_price; // Assuming this exists in your model
        $subTotal = $plan->has_discount ? $plan->total_discounted_price : $originalPrice;
        $discountAmount = $originalPrice - $subTotal;

        $total = $subTotal;
        $taxes = [];

        // 2. Tax Logic
        if ($this->taxSettings->enable_tax) {
            $taxAmount = ($this->taxSettings->tax_amount_type === 'percentage')
                ? ($subTotal * $this->taxSettings->tax_amount) / 100
                : $this->taxSettings->tax_amount;

            $taxes[] = [
                'name' => $this->taxSettings->tax_name, // e.g. "GST (18%)"
                'amount' => $taxAmount,
            ];

            // Add to total only if tax is exclusive (not included in price)
            if ($this->taxSettings->tax_type === 'exclusive') {
                $total += $taxAmount;
            }
        }

        // 3. Return Rich Array
        return [
            'plan_name' => $plan->name,
            'duration'  => $plan->duration ?? 12, // Default to 12 if null
            'currency_symbol' => $this->paymentSettings->currency_symbol,

            // Numbers
            'original_price' => $originalPrice,
            'has_discount' => $plan->has_discount,
            'discount_amount' => $discountAmount,
            'sub_total' => $subTotal,
            'taxes' => $taxes,
            'total' => $total,
        ];
    }
}
