<?php
declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AiSettings extends Settings
{
    public string $gemini_api_key;
    public string $model_name;
    public ?string $custom_model;

    public static function group(): string
    {
        return 'ai';
    }
}
