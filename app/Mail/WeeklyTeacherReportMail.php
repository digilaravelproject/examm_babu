<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyTeacherReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $teacherEmail;
    public $studentReports; // Collection of grouped reports

    /**
     * Create a new message instance.
     */
    public function __construct($teacherEmail, $studentReports)
    {
        $this->teacherEmail = $teacherEmail;
        $this->studentReports = $studentReports;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly Student Performance Report - Exam Babu',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.weekly_teacher_consolidated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
