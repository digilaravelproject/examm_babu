<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Settings\EmailSettings;
use App\Settings\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\ExamResultShareMail;

class ShareReportController extends Controller
{
    /**
     * 1. Send Email Logic
     */
    public function sendShareLink(Request $request, $sessionCode)
    {
        // 1. Cleaner Validation using 'required_without_all'
        $validated = $request->validate([
            'parent_email'     => 'required_without_all:instructor_email,self_copy|nullable|email',
            'instructor_email' => 'nullable|email',
            'self_copy'        => 'boolean'
        ], [
            'parent_email.required_without_all' => 'Please select at least one recipient.'
        ]);

        try {
            $session = ExamSession::where('code', $sessionCode)->with(['exam', 'user'])->firstOrFail();

            // 2. Generate Secure Signed Link (Valid for 30 days)
            $signedUrl = URL::temporarySignedRoute(
                'exam.share.public_view',
                now()->addDays(30),
                ['sessionCode' => $session->code]
            );

            $this->configureMailer();

            // 3. Build Recipient List
            $recipients = collect([
                $request->parent_email,
                $request->instructor_email,
                $request->self_copy ? $session->user->email : null
            ])->filter()->unique(); // Removes nulls and duplicates

            Log::info("Sharing result for Session: {$sessionCode}", ['recipients' => $recipients->toArray()]);

            // 4. Send Emails
            // Note: In high-traffic apps, change ->send() to ->queue()
            foreach ($recipients as $email) {
                Mail::to($email)->send(new ExamResultShareMail(
                    $session->user->first_name . ' ' . $session->user->last_name,
                    $session->exam->title,
                    $session->results['score'] ?? 0,
                    $session->exam->total_marks,
                    $signedUrl
                ));
            }

            return response()->json(['success' => true, 'message' => 'Report card sent successfully!']);

        } catch (\Exception $e) {
            Log::error("Share Exam Result Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send email. Please try again later.'], 500);
        }
    }

    /**
     * 2. Public Report View (Full Details)
     */
    public function viewPublicReport(Request $request, $sessionCode)
    {
        // Ensure the signed URL is valid (Double check if middleware is missing)
        if (!$request->hasValidSignature()) {
            abort(403, 'This link has expired or is invalid.');
        }

        $session = ExamSession::where('code', $sessionCode)
            ->with(['exam', 'user'])
            ->firstOrFail();

        $siteSettings = app(SiteSettings::class);

        // Get sections order
        $sections = $session->exam->examSections()->orderBy('section_order')->get();

        // 1. OPTIMIZATION: Fetch ALL questions for this session in ONE query
        $allQuestions = DB::table('exam_session_questions')
            ->join('questions', 'exam_session_questions.question_id', '=', 'questions.id')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->leftJoin('comprehension_passages', 'questions.comprehension_passage_id', '=', 'comprehension_passages.id')
            ->where('exam_session_questions.exam_session_id', $session->id)
            ->orderBy('exam_session_questions.sno', 'asc')
            ->select(
                'exam_session_questions.exam_section_id', // Needed for grouping
                'questions.id',
                'questions.solution',
                'questions.correct_answer',
                'question_types.code as type_code',
                'exam_session_questions.original_question as question_text',
                'exam_session_questions.options',
                'exam_session_questions.user_answer',
                'exam_session_questions.status',
                'exam_session_questions.is_correct',
                'exam_session_questions.marks_earned',
                'exam_session_questions.marks_deducted',
                'comprehension_passages.body as passage_body',
                'comprehension_passages.title as passage_title'
            )
            ->get();

        // 2. Group questions by Section ID using Laravel Collections
        $questionsBySection = $allQuestions->groupBy('exam_section_id');

        $reportData = [];

        foreach($sections as $section) {
            // Get questions for this section from the pre-fetched collection
            if(isset($questionsBySection[$section->id])) {

                $formattedQs = $questionsBySection[$section->id]->map(function($q) {
                    return (object) [
                        'id'             => $q->id,
                        'text'           => $q->question_text,
                        'type'           => $q->type_code,
                        'options'        => $this->safeUnserialize($q->options),
                        'user_answer'    => $this->safeUnserialize($q->user_answer),
                        'correct_answer' => $this->safeUnserialize($q->correct_answer),
                        'status'         => $q->status,
                        'is_correct'     => $q->is_correct,
                        'marks_earned'   => $q->marks_earned,
                        'marks_deducted' => $q->marks_deducted,
                        'explanation'    => $q->solution,
                        'passage'        => $q->passage_body ? ['title' => $q->passage_title, 'body' => $q->passage_body] : null
                    ];
                });

                $reportData[] = ['name' => $section->name, 'questions' => $formattedQs];
            }
        }

        return view('student.exams.public_report', compact('session', 'reportData', 'siteSettings'));
    }

    /**
     * Helper to Configure Mailer from DB
     */
    private function configureMailer()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) return;

            $settings = app(EmailSettings::class);

            if ($settings->host) {
                $config = [
                    'transport'  => 'smtp',
                    'host'       => $settings->host,
                    'port'       => $settings->port,
                    'username'   => $settings->username,
                    'password'   => $settings->password,
                    'encryption' => $settings->encryption,
                    'timeout'    => null,
                    'local_domain' => env('MAIL_EHLO_DOMAIN'),
                ];

                Config::set('mail.mailers.smtp', $config);
                Config::set('mail.from.address', $settings->from_address);
                Config::set('mail.from.name', $settings->from_name);

                // Crucial: Clear the resolved instance so Laravel rebuilds the transport
                Mail::purge('smtp');
            }
        } catch (\Exception $e) {
            Log::error("Mailer Config Error: " . $e->getMessage());
        }
    }

    private function safeUnserialize($data) {
        if (is_array($data) || is_object($data)) return $data;
        if (is_string($data)) {
            // Check if it's JSON first
            $json = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) return $json;

            // Fallback to serialization (Legacy support)
            // suppressing errors slightly cleaner
            return @unserialize($data) ?: $data;
        }
        return $data;
    }
}
