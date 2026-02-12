<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RazorpaySettings extends Settings
{
    public string $key_id;
    public string $key_secret;
    public string $webhook_url;
    public string $webhook_secret;

    public static function group(): string
    {
        return 'razorpay';
    }
}
