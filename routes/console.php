<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
Schedule::command('reports:send-weekly-teacher')
        ->weeklyOn(1, '08:00')
        ->timezone('Asia/Kolkata');

// Critical for Shared Hosting (Hostinger): Runs queue without Supervisor
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
