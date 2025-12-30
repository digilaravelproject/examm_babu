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
        $subTotal = $plan->has_discount ? $plan->total_discounted_price : $plan->total_price;
        $total = $subTotal;
        $taxes = [];

        // Calculate Tax
        if ($this->taxSettings->enable_tax) {
            $taxAmount = ($this->taxSettings->tax_amount_type === 'percentage')
                ? ($subTotal * $this->taxSettings->tax_amount) / 100
                : $this->taxSettings->tax_amount;

            $taxes[] = [
                'name' => $this->taxSettings->tax_name,
                'amount' => $taxAmount,
            ];

            // Add to total only if exclusive
            if ($this->taxSettings->tax_type === 'exclusive') {
                $total += $taxAmount;
            }
        }

        return [
            'plan_name' => $plan->name,
            'sub_total' => $subTotal,
            'taxes' => $taxes,
            'total' => $total,
            'currency_symbol' => $this->paymentSettings->currency_symbol
        ];
    }
}
