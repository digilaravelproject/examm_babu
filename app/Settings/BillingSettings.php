<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BillingSettings extends Settings
{
    public string $vendor_name;
    public string $invoice_prefix;
    public string $address;
    public string $city;
    public string $state;
    public string $zip;
    public string $country;
    public string $phone_number;
    public string $vat_number;
    public bool $enable_invoicing;

    public static function group(): string
    {
        return 'billing';
    }
}
