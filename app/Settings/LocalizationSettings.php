<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LocalizationSettings extends Settings
{
    public string $default_locale;

    public string $default_direction;

    public string $default_timezone;

    public static function group(): string
    {
        return 'localization';
    }
}
