<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // App/Helpers folder ki saari .php files dhundo
        $files = glob(app_path('Helpers') . '/*.php');

        // Har file ko load karo
        foreach ($files as $file) {
            require_once $file;
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
