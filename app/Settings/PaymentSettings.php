<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public string $default_payment_processor;

    public string $default_currency;

    public string $currency_symbol;

    public string $currency_symbol_position;

    public bool $enable_bank;

    public bool $enable_paypal;

    public bool $enable_stripe;

    public bool $enable_razorpay;

    public static function group(): string
    {
        return 'payments';
    }
}
