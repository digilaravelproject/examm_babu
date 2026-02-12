<?php
declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomePageSettings extends Settings
{
    public bool $enable_top_bar;
    public bool $enable_search;
    public bool $enable_hero;
    public bool $enable_features;
    public bool $enable_categories;
    public bool $enable_stats;
    public bool $enable_testimonials;
    public bool $enable_cta;
    public bool $enable_footer;

    public static function group(): string
    {
        return 'home_page';
    }
}
