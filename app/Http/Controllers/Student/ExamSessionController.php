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

    public function startExam(Request $request, $scheduleId)
    {
        try {
            $user = $request->user();
            $schedule = ExamSchedule::with(['exam.examSections', 'exam.questions'])->findOrFail($scheduleId);
            $exam = $schedule->exam;

            // Check Existing Session
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

            // Check Attempts
            $attemptsCount = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->where('status', 'completed')
                ->count();

            $maxAttempts = $exam->settings['no_of_attempts'] ?? 0;
            if ($maxAttempts > 0 && $attemptsCount >= $maxAttempts) {
                return redirect()->back()->with('error', __('max_attempts_text'));
            }

            // Access Check
            $accessCheck = $this->repository->checkAccess($schedule, $user);
            if (!$accessCheck['allowed']) {
                return redirect()->back()->with('error', $accessCheck['message']);
            }

            // Wallet Check
            $hasSubscription = $user->hasActiveSubscription($exam->sub_category_id, 'exams');
            if ($exam->is_paid && !$hasSubscription && $exam->can_redeem) {
                if ($user->balance < $exam->points_required) {
                    return redirect()->back()->with('error', __('insufficient_points'));
                }
                $user->withdraw($exam->points_required, ['description' => 'Attempt: ' . $exam->title]);
            }

            // Create Session
            $session = $this->repository->createSession($exam, $schedule, $user);

            return redirect()->route('student.exam.interface', $session->code);

        } catch (\Throwable $e) {
            Log::error("Start Exam Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error starting exam. Please try again.');
        }
    }

    public function loadInterface($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)
            ->with(['exam'])
            ->firstOrFail();

        if ($session->user_id !== Auth::id()) abort(403);

        $remainingSeconds = now()->diffInSeconds($session->ends_at, false);

        // Auto Finish if Time is Up
        if ($remainingSeconds <= 0 && $session->status !== 'completed') {
            $session->status = 'completed';
            $session->completed_at = now();
            $session->save();
            return redirect()->route('student.exams.result', $session->id);
        }

        if ($session->status === 'completed') {
            return redirect()->route('student.exams.result', $session->id);
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

    public function fetchSectionQuestions($sessionCode, $sectionId)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();

        // Fetch from Session Snapshot
        $questionsData = DB::table('exam_session_questions')
            ->join('questions', 'exam_session_questions.question_id', '=', 'questions.id')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
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
                'exam_session_questions.marks_deducted'
            )
            ->get();

        $formatted = $questionsData->map(function($q) {
            // Decode options safely
            $options = $q->options;
            if (is_string($options)) {
                $decoded = json_decode($options, true);
                $options = (json_last_error() === JSON_ERROR_NONE) ? $decoded : @unserialize($options);
            }

            return [
                'id' => $q->id,
                'text' => $q->question_text,
                'options' => $options,
                'type' => $q->type_code,
                'status' => $q->status,
                'selected_option' => $q->user_answer ? unserialize($q->user_answer) : null,
                'marks' => $q->marks_earned, // Just for display
                'negative' => $q->marks_deducted
            ];
        });

        // Mark Section Visited
        DB::table('exam_session_sections')
            ->where('exam_session_id', $session->id)
            ->where('exam_section_id', $sectionId)
            ->update(['status' => 'visited']);

        return response()->json(['questions' => $formatted]);
    }

    public function saveAnswer(ExamUpdateAnswerRequest $request, $sessionCode)
    {
        try {
            $session = ExamSession::with('exam')->where('code', $sessionCode)->firstOrFail();

            $question = Question::with('questionType')->find($request->question_id);
            $section = ExamSection::find($request->section_id);

            // Scoring
            $isCorrect = false;
            if ($request->status == 'answered' || $request->status == 'answered_mark_for_review') {
                $isCorrect = $this->repository->evaluateAnswer($question, $request->user_answer);
            }

            $marks = $this->repository->calculateMarks($session->exam, $section, $question, $isCorrect);

            // Update Pivot (No Timestamps)
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

            // Update Session
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

    public function finishExam($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();

        if ($session->status == 'completed') {
             return response()->json(['redirect' => route('student.exams.result', $session->id)]);
        }

        $session->status = 'completed';
        $session->completed_at = now();
        $session->save();

        return response()->json(['redirect' => route('student.exams.result', $session->id)]);
    }
}
