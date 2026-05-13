<?php

namespace App\Mail;

use App\Settings\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // ✅ Essential for performance
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExamResultShareMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $studentName;
    public string $examTitle;
    public $score;     // Can be int or float
    public $totalMarks; // Can be int or float
    public string $link;
    public $settings;   // ✅ Added for dynamic logo/branding

    /**
     * Create a new message instance.
     */
    public function __construct(string $studentName, string $examTitle, $score, $totalMarks, string $link)
    {
        $this->studentName = $studentName;
        $this->examTitle = $examTitle;
        $this->score = $score;
        $this->totalMarks = $totalMarks;
        $this->link = $link;

        // ✅ Automatically resolve site settings for the email view
        $this->settings = app(SiteSettings::class);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Calculate percentage for subject line (optional, but looks nice)
        $percentage = ($this->totalMarks > 0)
            ? round(($this->score / $this->totalMarks) * 100)
            : 0;

        return $this->subject("Report Card: {$this->studentName} - {$this->examTitle} ({$percentage}%)")
                    ->markdown('emails.exam_share')
                    ->with([
                        'logo' => $this->settings->logo_url ?? null,
                        'school_name' => $this->settings->site_name ?? 'The Academy'
                    ]);
    }
}
