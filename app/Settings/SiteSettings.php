<?php
declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $app_name;
    public string $tag_line;
    public string $seo_description;
    public ?string $logo_path; // Nullable agar path set na ho
    public ?string $white_logo_path;
    public ?string $favicon_path;
    public bool $can_register;

    public static function group(): string
    {
        return 'site';
    }
}
