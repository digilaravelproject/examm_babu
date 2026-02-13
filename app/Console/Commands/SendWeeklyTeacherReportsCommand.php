<?php

namespace App\Console\Commands;

use App\Mail\WeeklyTeacherReportMail;
use App\Models\TeacherReportQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyTeacherReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-weekly-teacher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send consolidated weekly performance reports to teachers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Weekly Teacher Reports...');

        // 1. Fetch Pending Reports grouped by Teacher Email
        // We act on 'pending' records.
        $pendingReports = TeacherReportQueue::where('status', 'pending')
            ->orderBy('teacher_email')
            ->get();

        if ($pendingReports->isEmpty()) {
            $this->info('No pending reports found.');
            return;
        }

        // 2. Group by Teacher
        $groupedByTeacher = $pendingReports->groupBy('teacher_email');

        $this->info("Found " . $groupedByTeacher->count() . " teachers to email.");

        foreach ($groupedByTeacher as $teacherEmail => $reports) {
            try {
                $this->info("Sending email to: $teacherEmail (" . $reports->count() . " students)");

                // 3. Send Consolidated Email
                Mail::to($teacherEmail)->send(new WeeklyTeacherReportMail($teacherEmail, $reports));

                // 4. Mark as Sent
                TeacherReportQueue::whereIn('id', $reports->pluck('id'))->update([
                    'status' => 'sent',
                    'updated_at' => now()
                ]);

            } catch (\Exception $e) {
                $this->error("Failed to send to $teacherEmail: " . $e->getMessage());
                Log::error("Weekly Teacher Report Failed for $teacherEmail: " . $e->getMessage());

                // Optionally mark as failed
                TeacherReportQueue::whereIn('id', $reports->pluck('id'))->update(['status' => 'failed']);
            }
        }

        $this->info('Weekly Teacher Reports Completed.');
    }
}
