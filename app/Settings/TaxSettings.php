<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TaxSettings extends Settings
{
    // Primary Tax
    public bool $enable_tax;
    public string $tax_name;
    public string $tax_type;        // 'exclusive' or 'inclusive'
    public string $tax_amount_type; // 'percentage' or 'fixed'
    public float $tax_amount;

    // Additional Tax (Yeh missing tha, isliye error aaya)
    public bool $enable_additional_tax;
    public string $additional_tax_name;
    public string $additional_tax_type;
    public string $additional_tax_amount_type;
    public float $additional_tax_amount;

    public static function group(): string
    {
        return 'tax';
    }
}
