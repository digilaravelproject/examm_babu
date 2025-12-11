<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
// Yeh add kiya taaki DocBlock mein use kar sakein
use Illuminate\Database\Eloquent\Model;

class LogSuccessfulLogout
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
     * @param  \Illuminate\Auth\Events\Logout  $event
     * @return void
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            // Fix: IDE ko batane ke liye ki yeh variable ek Model hai
            /** @var Model $user */
            $user = $event->user;

            activity()
                ->causedBy($user)
                ->log('User Logged Out');
        }
    }
}
