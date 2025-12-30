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

    /**
     * Calculate order price, discounts, and taxes.
     */
    public function orderSummary($plan): array
    {
        $subTotal = 0;
        $total = 0;
        $taxes = [];

        // ---------------------------------------------------------
        // 1. Price Logic
        // ---------------------------------------------------------
        $basePrice = (float) ($plan->price ?? 0);

        // Discount Logic
        $hasDiscount = (bool) ($plan->has_discount ?? false);
        $discountPercentage = (float) ($plan->discount_percentage ?? 0);

        // Calculate Discount Amount
        $discountAmount = 0;
        if ($hasDiscount && $discountPercentage > 0) {
            $discountAmount = ($basePrice * $discountPercentage) / 100;
        }

        // Subtotal after discount
        $subTotal = $basePrice - $discountAmount;
        $total = $subTotal;

        // ---------------------------------------------------------
        // 2. Tax Calculation Logic
        // ---------------------------------------------------------

        // Main Tax
        if ($this->taxSettings->enable_tax ?? false) {
            $taxRate = $this->taxSettings->tax_amount ?? 0;
            $taxType = $this->taxSettings->tax_amount_type ?? 'percentage';

            $taxAmount = ($taxType === 'percentage')
                ? ($subTotal * $taxRate) / 100
                : $taxRate;

            $taxes[] = [
                'name' => ($this->taxSettings->tax_name ?? 'Tax') . ' (' . $taxRate . '%)',
                'amount' => $taxAmount,
            ];

            if (($this->taxSettings->tax_type ?? 'exclusive') === 'exclusive') {
                $total += $taxAmount;
            }
        }

        // Additional Tax
        if ($this->taxSettings->enable_additional_tax ?? false) {
            $addTaxRate = $this->taxSettings->additional_tax_amount ?? 0;
            $addTaxType = $this->taxSettings->additional_tax_amount_type ?? 'percentage';

            $addTaxAmount = ($addTaxType === 'percentage')
                ? ($subTotal * $addTaxRate) / 100
                : $addTaxRate;

            $taxes[] = [
                'name' => ($this->taxSettings->additional_tax_name ?? 'Extra Tax') . ' (' . $addTaxRate . '%)',
                'amount' => $addTaxAmount,
            ];

            if (($this->taxSettings->additional_tax_type ?? 'exclusive') === 'exclusive') {
                $total += $addTaxAmount;
            }
        }

        // ---------------------------------------------------------
        // 3. Return Final Data Structure
        // ---------------------------------------------------------
        return [
            'plan_name'       => $plan->name,
            'duration'        => $plan->duration ?? 1,
            'currency_symbol' => $this->paymentSettings->currency_symbol ?? '₹',

            // Pricing Details
            'original_price'      => $basePrice,
            'has_discount'        => $hasDiscount,
            'discount_percentage' => $discountPercentage, // <--- Yeh add kiya hai
            'discount_amount'     => $discountAmount,

            // Totals
            'sub_total'       => $subTotal,
            'taxes'           => $taxes,
            'total'           => $total,
        ];
    }
}
