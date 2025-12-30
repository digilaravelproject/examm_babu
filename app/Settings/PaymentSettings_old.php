<?php
declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings_old extends Settings
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
