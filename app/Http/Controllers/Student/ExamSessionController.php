<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ExamUpdateAnswerRequest;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\ExamSection;
use App\Repositories\UserExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamSessionController extends Controller
{
    protected $repository;

    public function __construct(UserExamRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 1. Start Exam
     * Checks constraints (Attempts, Time, Wallet) and creates a session.
     */
    public function startExam(Request $request, $scheduleId)
    {
        try {
            $user = $request->user();
            $schedule = ExamSchedule::with(['exam.examSections', 'exam.questions'])->findOrFail($scheduleId);
            $exam = $schedule->exam;

            // A. Check Existing Session (Resume)
            $existingSession = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->whereIn('status', ['started', 'paused'])
                ->first();

            if ($existingSession) {
                if ($existingSession->status === 'paused') {
                    $existingSession->update(['status' => 'started']);
                }
                return redirect()->route('student.exam.interface', $existingSession->code);
            }

            // B. Check Attempts
            $attemptsCount = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->whereIn('status', ['completed', 'terminated'])
                ->count();

            $maxAttempts = $exam->settings['no_of_attempts'] ?? 0;
            if ($maxAttempts > 0 && $attemptsCount >= $maxAttempts) {
                return redirect()->back()->with('error', __('max_attempts_text'));
            }

            // C. Validate Access
            $accessCheck = $this->repository->checkAccess($schedule, $user);
            if (!$accessCheck['allowed']) {
                return redirect()->back()->with('error', $accessCheck['message']);
            }

            // D. Wallet / Subscription Check
            $hasSubscription = $user->hasActiveSubscription($exam->sub_category_id, 'exams');
            if ($exam->is_paid && !$hasSubscription && $exam->can_redeem) {
                if ($user->balance < $exam->points_required) {
                    return redirect()->back()->with('error', __('insufficient_points'));
                }
                $user->withdraw($exam->points_required, ['description' => 'Attempt: ' . $exam->title]);
            }

            // E. Create Session
            $session = $this->repository->createSession($exam, $schedule, $user);

            return redirect()->route('student.exam.interface', $session->code);

        } catch (\Throwable $e) {
            Log::error("Start Exam Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error starting exam.');
        }
    }

    /**
     * 2. Load Interface
     * Loads the main exam screen. Handles redirection if expired/completed.
     */
    public function loadInterface($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->with(['exam'])->firstOrFail();

        if ($session->user_id !== Auth::id()) abort(403);

        // If Exam Terminated (Cheating), send to Dashboard
        if ($session->status === 'terminated') {
            return redirect()->route('student.dashboard')->with('error', 'Exam was terminated due to malpractice.');
        }

        // If Exam Completed, send to Result
        if ($session->status === 'completed') {
            return redirect()->route('student.exams.result', $session->id);
        }

        // Check Timer
        $remainingSeconds = now()->diffInSeconds($session->ends_at, false);
        if ($remainingSeconds <= 0) {
            return $this->finishExamLogic($session); // Auto Submit
        }

        $sections = $session->exam->examSections()
            ->orderBy('section_order')
            ->get(['id', 'name', 'total_questions']);

        return view('student.exams.interface', [
            'session' => $session,
            'exam' => $session->exam,
            'sections' => $sections,
            'remainingSeconds' => max(0, $remainingSeconds),
            'user' => Auth::user()
        ]);
    }

    /**
     * 3. Fetch Questions (AJAX)
     * Returns Section Questions + Options + Passage Data.
     */
    public function fetchSectionQuestions($sessionCode, $sectionId)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();

        $questionsData = DB::table('exam_session_questions')
            ->join('questions', 'exam_session_questions.question_id', '=', 'questions.id')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            // Left Join for Passage Data
            ->leftJoin('comprehension_passages', 'questions.comprehension_passage_id', '=', 'comprehension_passages.id')
            ->where('exam_session_questions.exam_session_id', $session->id)
            ->where('exam_session_questions.exam_section_id', $sectionId)
            ->orderBy('exam_session_questions.sno', 'asc')
            ->select(
                'questions.id',
                'question_types.code as type_code',
                'exam_session_questions.original_question as question_text',
                'exam_session_questions.options',
                'exam_session_questions.status',
                'exam_session_questions.user_answer',
                'exam_session_questions.marks_earned',
                'exam_session_questions.marks_deducted',
                // Passage Columns
                'comprehension_passages.title as passage_title',
                'comprehension_passages.body as passage_body'
            )
            ->get();

        $formatted = $questionsData->map(function($q) {
            // Decode options carefully
            $options = $q->options;
            if (is_string($options)) {
                $decoded = json_decode($options, true);
                $options = (json_last_error() === JSON_ERROR_NONE) ? $decoded : @unserialize($options);
            }

            return [
                'id' => $q->id,
                'text' => $q->question_text,
                'options' => $options, // Returns Array of Objects {option, image}
                'type' => $q->type_code,
                'status' => $q->status,
                'selected_option' => $q->user_answer ? unserialize($q->user_answer) : null,
                'marks' => $q->marks_earned,
                'negative' => $q->marks_deducted,
                // Attach Passage if exists
                'passage' => $q->passage_body ? [
                    'title' => $q->passage_title,
                    'body' => $q->passage_body
                ] : null
            ];
        });

        // Mark Section as Visited
        DB::table('exam_session_sections')
            ->where('exam_session_id', $session->id)
            ->where('exam_section_id', $sectionId)
            ->update(['status' => 'visited']);

        return response()->json(['questions' => $formatted]);
    }

    /**
     * 4. Save Answer (AJAX)
     * Validates and saves answer immediately.
     */
    public function saveAnswer(ExamUpdateAnswerRequest $request, $sessionCode)
    {
        try {
            log::info("save answer working;");
            $session = ExamSession::with('exam')->where('code', $sessionCode)->firstOrFail();
            $question = Question::with('questionType')->find($request->question_id);
            $section = ExamSection::find($request->section_id);

            // Calculate correctness
            $isCorrect = false;
            if ($request->status == 'answered' || $request->status == 'answered_mark_for_review') {
                $isCorrect = $this->repository->evaluateAnswer($question, $request->user_answer);
            }

            // Calculate Marks
            $marks = $this->repository->calculateMarks($session->exam, $section, $question, $isCorrect);

            // Update Pivot
            DB::table('exam_session_questions')
                ->where('exam_session_id', $session->id)
                ->where('question_id', $question->id)
                ->update([
                    'user_answer' => serialize($request->user_answer),
                    'status' => $request->status,
                    'is_correct' => $isCorrect,
                    'marks_earned' => $marks['earned'],
                    'marks_deducted' => $marks['deducted'],
                    'time_taken' => DB::raw("time_taken + " . (int)$request->time_taken)
                ]);

            // Update Session Stats
            $session->update([
                'current_section' => $request->section_id,
                'current_question' => $request->question_id,
                'total_time_taken' => $request->total_time_taken ?? $session->total_time_taken
            ]);

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error("Save Answer Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to save answer'], 500);
        }
    }

    /**
     * 5. Terminate Exam (Violation)
     * Redirects to Dashboard immediately.
     */
    public function terminateExam($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();

        $session->status = 'terminated';
        $session->completed_at = now();
        $session->save();

        return response()->json(['redirect' => route('student.dashboard')]);
    }

    /**
     * 6. Finish Exam (Submit)
     * Calculates Result and Redirects to Result Page.
     */
    public function finishExam($sessionCode)
    {
        log::info("finish exams working");
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();
        return $this->finishExamLogic($session);
    }

    private function finishExamLogic($session)
    {
          log::info("finish exams logic working");
        if ($session->status !== 'completed' && $session->status !== 'terminated') {
            $session->status = 'completed';
            $session->completed_at = now();

            // Calculate and Store Results
            // This relies on the function I added to your Repository
            $session->results = $this->repository->sessionResults($session, $session->exam);

            $session->save();
        }

        if (request()->wantsJson()) {
            return response()->json(['redirect' => route('student.exams.result', $session->id)]);
        }

        return redirect()->route('student.exams.result', $session->id);
    }

    /**
     * 7. Show Result Page (Fixes 500 Error)
     */
    public function showResult($sessionId)
    {
        // Eager load exam and sections for the view
        $session = ExamSession::with(['exam', 'sections'])->findOrFail($sessionId);

        // Security check: Don't show result if terminated
        if($session->status === 'terminated') {
            return redirect()->route('student.dashboard')->with('error', 'Exam Terminated due to policy violation.');
        }

        return view('student.exams.result', compact('session'));
    }
}
