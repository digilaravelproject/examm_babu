<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CategorySettings extends Settings
{
    // Default values add kar diye hain taaki "Uninitialized Property" error na aaye
    public string $title = 'Select Category';

    public string $subtitle = 'Please choose your preferred exam category to continue';

    public int $limit = 50;

    public static function group(): string
    {
        return 'category';
    }
}
