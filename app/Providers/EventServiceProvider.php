<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
// IMPORTANT: Yeh niche wali line change ki hai. Normal 'ServiceProvider' extend karne se $listen array kaam nahi karega.
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            LogSuccessfulLogin::class,
        ],
        Logout::class => [
            LogSuccessfulLogout::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
