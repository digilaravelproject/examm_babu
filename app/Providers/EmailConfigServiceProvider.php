<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Settings\EmailSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class EmailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 1. Dynamic Configuration Logic
        try {
            if (Schema::hasTable('settings')) {

                $settings = app(EmailSettings::class);

                if ($settings->host) {

                    // Force SMTP
                    Config::set('mail.default', 'smtp');

                    // Set SMTP Details
                    Config::set('mail.mailers.smtp.host', $settings->host);
                    Config::set('mail.mailers.smtp.port', $settings->port);
                    Config::set('mail.mailers.smtp.encryption', $settings->encryption);
                    Config::set('mail.mailers.smtp.username', $settings->username);
                    Config::set('mail.mailers.smtp.password', $settings->password);

                    // Set From Address
                    Config::set('mail.from.address', $settings->from_address);
                    Config::set('mail.from.name', $settings->from_name);

                    // Log::info('EmailConfigServiceProvider: SMTP Configured via DB.');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Email Config Error: ' . $e->getMessage());
        }

        // 2. Custom Email View Logic (Jo aapne verify-email ke liye banaya)
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Welcome to Exam Babu! Verify Your Email')
                ->view('emails.verify-email', ['url' => $url, 'user' => $notifiable]);
        });
    }
}
