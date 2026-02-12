<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReferralSettings extends Settings
{
    public bool $enable_referral;

    // Naya User pehli baar laye to ye % (High, e.g. 20%)
    public float $commission_percentage;

    // Purana User wapas aaye (Exam buy kare) to ye % (Low, e.g. 5%)
    public float $recurring_commission_percentage;

    // Security: User se dobara commission lene ke liye kitne din rukna padega? (e.g. 7 days)
    public int $spam_protection_days;

    public float $min_withdrawal_amount;
    public int $cookie_lifetime_days;

    public static function group(): string
    {
        return 'referral';
    }
}
