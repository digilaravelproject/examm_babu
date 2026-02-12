<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;   // ✅ THIS WAS MISSING
use App\View\Composers\NavbarComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(
            'store.partials.navbar',
            NavbarComposer::class
        );
    }
}
