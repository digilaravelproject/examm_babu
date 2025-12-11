<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
// Yeh add kiya taaki DocBlock mein use kar sakein
use Illuminate\Database\Eloquent\Model;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event): void
    {
        // Fix: IDE ko batane ke liye ki yeh variable ek Model hai
        /** @var Model $user */
        $user = $event->user;

        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'agent' => request()->userAgent()
            ])
            ->log('User Logged In');
    }
}
