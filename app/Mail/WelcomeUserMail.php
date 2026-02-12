<?php

namespace App\Mail;

use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $password; // Raw password show karne ke liye
    public string $role;
    public $settings;        // Branding ke liye

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $password, string $role)
    {
        $this->user = $user;
        $this->password = $password;
        $this->role = ucfirst($role); // Role ka first letter capital karne ke liye
        $this->settings = app(SiteSettings::class);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $siteName = $this->settings->site_name ?? 'Exam Babu';

        return $this->subject("Welcome to {$siteName} - Your Login Details")
                    ->markdown('emails.welcome_user') // Hum markdown view use karenge
                    ->with([
                        'logo' => $this->settings->logo_url ?? null,
                        'school_name' => $siteName,
                        'login_url' => route('login'), // Login route
                    ]);
    }
}
