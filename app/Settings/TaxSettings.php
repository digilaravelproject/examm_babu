<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TaxSettings extends Settings
{
    public bool $enable_tax;
    public string $tax_name;
    public string $tax_type; // 'exclusive' or 'inclusive'
    public string $tax_amount_type; // 'percentage' or 'fixed'
    public float $tax_amount;

    public static function group(): string
    {
        return 'tax';
    }
}
