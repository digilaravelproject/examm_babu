<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public string $default_currency;
    public string $currency_symbol;
    public string $currency_symbol_position;

    public static function group(): string
    {
        return 'payments';
    }
}
