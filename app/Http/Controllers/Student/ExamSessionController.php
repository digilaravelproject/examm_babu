<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ExamUpdateAnswerRequest;
use App\Models\ExamSchedule;
use App\Models\ExamSection;
use App\Models\ExamSession;
use App\Models\Advertisement;
use App\Models\Question;
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
     */
    public function startExam(Request $request, $scheduleId)
    {
        try {
            $user = $request->user();
            $schedule = ExamSchedule::with(['exam.examSections', 'exam.questions'])->findOrFail($scheduleId);
            $exam = $schedule->exam;

            if ($exam->total_duration < 60) {
                return redirect()->back()->with('error', 'Exam configuration error: Total duration is invalid (0 mins). Please contact support.');
            }

            $isAdmin = $user->hasRole('admin') || $user->hasRole('instructor');

            $existingSession = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->whereIn('status', ['started', 'paused'])
                ->first();

            if ($existingSession) {
                if (now()->gt($existingSession->ends_at)) {
                    $existingSession->status = 'completed';
                    $existingSession->completed_at = now();
                    $existingSession->results = $this->repository->sessionResults($existingSession, $exam);
                    $existingSession->save();
                } else {
                    if ($existingSession->status === 'paused') {
                        $existingSession->update(['status' => 'started']);
                    }
                    return redirect()->route('student.exam.interface', $existingSession->code);
                }
            }

            $attemptsCount = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->whereIn('status', ['completed', 'terminated'])
                ->count();

            $maxAttempts = $exam->settings['no_of_attempts'] ?? 0;

            if (! $isAdmin && $maxAttempts > 0 && $attemptsCount >= $maxAttempts) {
                return redirect()->back()->with('error', 'Maximum attempts reached.');
            }

            $accessCheck = $this->repository->checkAccess($schedule, $user);
            if (! $isAdmin && ! $accessCheck['allowed']) {
                return redirect()->back()->with('error', $accessCheck['message']);
            }

            $hasSubscription = \App\Models\Subscription::query()
                ->where('user_id', $user->id)
                ->where('category_id', $exam->micro_category_id)
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->exists();

            if (! $isAdmin && $exam->is_paid && ! $hasSubscription) {
                if ($exam->can_redeem) {
                    if ($user->balance < $exam->points_required) {
                        return redirect()->back()->with('error', 'Insufficient points and no active subscription.');
                    }
                    $user->withdraw($exam->points_required, ['description' => 'Attempt: ' . $exam->title]);
                } else {
                    return redirect()->back()->with('error', 'You need an active subscription plan to access this exam.');
                }
            }

            $session = $this->repository->createSession($exam, $schedule, $user);

            return redirect()->route('student.exam.interface', $session->code);
        } catch (\Throwable $e) {
            Log::error('Start Exam Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error starting exam: ' . $e->getMessage());
        }
    }

    /**
     * 2. Load Interface
     */
    public function loadInterface($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->with(['exam'])->firstOrFail();

        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        if ($session->status === 'terminated') {
            $redirect = Auth::user()->hasRole('admin') ? 'admin.dashboard' : 'student.dashboard';
            return redirect()->route($redirect)->with('error', 'Exam was terminated due to malpractice.');
        }

        if ($session->status === 'completed') {
            return redirect()->route('student.exams.result', $session->id);
        }

        // --- FIX: Self-Healing for PREVIEW Sessions ---
        // If it's a preview session, ensure the duration is correct (calculating from sections)
        // This fixes existing previews that were created with the old 24-hour logic
        if (str_starts_with($session->code, 'PREVIEW-')) {
            $totalDuration = $session->exam->examSections()->sum('total_duration');
            if ($totalDuration > 0) {
                $expectedEndsAt = \Carbon\Carbon::parse($session->starts_at)->addSeconds((int) $totalDuration);

                // If the current ends_at is significantly different (e.g., > 1 minute diff), fix it
                if ($session->ends_at->diffInMinutes($expectedEndsAt) > 1) {
                    $session->ends_at = $expectedEndsAt;
                    $session->save();
                }
            }
        }

        $remainingSeconds = now()->diffInSeconds($session->ends_at, false);

        if ($remainingSeconds <= 0) {
            return $this->finishExamLogic($session);
        }

        $sections = $session->exam->examSections()
            ->orderBy('section_order')
            ->get(['id', 'name', 'total_questions', 'allow_translation', 'translation_language']);

        // Return View
        return view('student.exams.interface', [
            'session' => $session,
            'exam' => $session->exam,
            'sections' => $sections,
            'remainingSeconds' => (int) max(0, $remainingSeconds),
            'user' => Auth::user(),
        ]);
    }

    /**
     * 3. Fetch Questions (AJAX)
     */
    public function fetchSectionQuestions($sessionCode, $sectionId)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();
        $section = ExamSection::findOrFail($sectionId);

        $questionsData = DB::table('exam_session_questions')
            ->join('questions', 'exam_session_questions.question_id', '=', 'questions.id')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
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
                'questions.default_marks as marks_earned',
                'comprehension_passages.title as passage_title',
                'comprehension_passages.body as passage_body'
            )
            ->get();

        $formatted = $questionsData->map(function ($q) use ($section) {
            $options = $q->options;
            if (is_string($options)) {
                $decoded = json_decode($options, true);
                $options = (json_last_error() === JSON_ERROR_NONE) ? $decoded : @unserialize($options);
            }
            if (!is_array($options)) {
                $options = [];
            }

            // --- FIX: MTF & ORD formatting with IDs ---
            if ($q->type_code === 'MTF') {
                $matches = [];
                $pairs = [];
                // Add Unique ID to each option if not present, based on index
                foreach ($options as $idx => $opt) {
                    $id = is_array($opt) ? ($opt['id'] ?? $idx) : $idx; // Ensure ID exists
                    $val = is_array($opt) ? ($opt['option'] ?? '') : '';

                    if (is_string($val) && str_contains($val, ',')) {
                        $parts = explode(',', $val);
                        $left = trim($parts[0]);
                        $right = trim($parts[1] ?? '');
                    } else {
                        $left = $val;
                        $right = is_array($opt) ? ($opt['pair'] ?? $opt['match'] ?? '') : '';
                    }
                    $matches[] = ['id' => $id, 'value' => $left];
                    $pairs[] = ['id' => $id, 'value' => $right];
                }
                $options = ['matches' => $matches, 'pairs' => $pairs];
            }

            if ($q->type_code === 'ORD') {
                $options = array_values($options); // Ensure sequential indices
                $options = array_map(function ($o, $i) {
                    // Ensure ID exists for tracking
                    $id = is_array($o) ? ($o['id'] ?? $i) : $i;
                    $value = is_array($o) ? ($o['option'] ?? '') : (is_string($o) ? $o : '');
                    return ['id' => $id, 'value' => $value];
                }, $options, array_keys($options));
            }

            // --- SECURITY: Strip sensitive fields from options ---
            if (is_array($options)) {
                // For nested structures like MTF
                if (isset($options['matches']) && isset($options['pairs'])) {
                    $options['matches'] = array_map(function($o) {
                        if (is_array($o)) { unset($o['is_correct'], $o['answer'], $o['correct_answer']); }
                        return $o;
                    }, $options['matches']);
                    $options['pairs'] = array_map(function($o) {
                        if (is_array($o)) { unset($o['is_correct'], $o['answer'], $o['correct_answer']); }
                        return $o;
                    }, $options['pairs']);
                } else {
                    // Standard list of options (Radio/Checkbox)
                    $options = array_map(function($o) {
                        if (is_array($o)) { unset($o['is_correct'], $o['answer'], $o['correct_answer']); }
                        return $o;
                    }, $options);
                }
            }

            return [
                'id' => $q->id,
                'text' => $q->question_text,
                'options' => $options,
                'type_code' => $q->type_code,
                'status' => $q->status,
                'selected_option' => $q->user_answer ? unserialize($q->user_answer) : null,
                'marks' => $q->marks_earned,
                'allow_translation' => (bool) $section->allow_translation,
                'passage' => $q->passage_body ? [
                    'title' => $q->passage_title,
                    'body' => $q->passage_body,
                ] : null,
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
     */
    public function saveAnswer(ExamUpdateAnswerRequest $request, $sessionCode)
    {
        try {
            $session = ExamSession::with('exam')->where('code', $sessionCode)->firstOrFail();

            if ($session->status === 'completed' || $session->status === 'terminated') {
                return response()->json(['error' => 'Session already closed'], 403);
            }

            // Load full question model to get 'question' text for FIB regex
            $question = Question::with('questionType')->findOrFail($request->question_id);
            $section = ExamSection::findOrFail($request->section_id);

            // Correctness evaluation
            $isCorrect = false;
            if ($request->status == 'answered' || $request->status == 'answered_mark_for_review') {
                $isCorrect = $this->repository->evaluateAnswer($question, $request->user_answer);
            }

            // Calculate Marks
            $marks = $this->repository->calculateMarks($session->exam, $section, $question, $isCorrect);

            // Update Question State
            DB::table('exam_session_questions')
                ->where('exam_session_id', $session->id)
                ->where('question_id', $question->id)
                ->update([
                    'user_answer' => serialize($request->user_answer),
                    'status' => $request->status,
                    'is_correct' => $isCorrect,
                    'marks_earned' => $marks['earned'],
                    'marks_deducted' => $marks['deducted'],
                    'time_taken' => DB::raw('time_taken + ' . (int) $request->time_taken),
                ]);

            // Update Global Session Stats
            $session->update([
                'current_section' => $request->section_id,
                'current_question' => $request->question_id,
                'total_time_taken' => $request->total_time_taken ?? $session->total_time_taken,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Save Answer Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 5. Terminate Exam (Violation)
     */
    public function terminateExam($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();

        $session->status = 'terminated';
        $session->completed_at = now();
        $session->save();

        $redirect = Auth::user()->hasRole('admin') ? route('admin.dashboard') : route('student.dashboard');

        return response()->json(['redirect' => $redirect]);
    }

    /**
     * 6. Finish Exam (Submit)
     */
    public function finishExam($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();
        return $this->finishExamLogic($session);
    }

    private function finishExamLogic($session)
    {
        if ($session->status !== 'completed' && $session->status !== 'terminated') {
            $session->status = 'completed';
            $session->completed_at = now();
            $session->results = $this->repository->sessionResults($session, $session->exam);
            $session->save();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('student.exams.result', $session->id),
            ]);
        }

        return redirect()->route('student.exams.result', $session->id);
    }

    /**
     * 7. Show Result Page
     */
    public function showResult($sessionId)
    {
        $session = ExamSession::with(['exam', 'sections'])->findOrFail($sessionId);

        if ($session->status === 'terminated') {
            $redirect = Auth::user()->hasRole('admin') ? 'admin.dashboard' : 'student.dashboard';
            return redirect()->route($redirect)->with('error', 'Exam Terminated.');
        }

        return view('student.exams.result', compact('session'));
    }

    /**
     * 8. Show Review Page (Authenticated Student/Admin)
     */
    public function showReview($sessionId)
    {
        $session = ExamSession::where('id', $sessionId)
            ->with(['exam', 'user'])
            ->firstOrFail();

        // Security: Only owner or admin/instructor can view
        if ($session->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'instructor'])) {
            abort(403);
        }

        // Get sections order
        $sections = $session->exam->examSections()->orderBy('section_order')->get();

        // Prefetch ALL questions in one query (Optimization)
        $allQuestions = DB::table('exam_session_questions')
            ->join('questions', 'exam_session_questions.question_id', '=', 'questions.id')
            ->join('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->leftJoin('comprehension_passages', 'questions.comprehension_passage_id', '=', 'comprehension_passages.id')
            ->where('exam_session_questions.exam_session_id', $session->id)
            ->orderBy('exam_session_questions.sno', 'asc')
            ->select(
                'exam_session_questions.exam_section_id',
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

        $questionsBySection = $allQuestions->groupBy('exam_section_id');
        $reportData = [];

        foreach($sections as $section) {
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

        $siteSettings = app(\App\Settings\SiteSettings::class);
        $advertisement = Advertisement::active()->where('location', 'report_banner')->inRandomOrder()->first();

        return view('student.exams.public_report', compact('session', 'reportData', 'siteSettings', 'advertisement'));
    }

    private function safeUnserialize($data) {
        if (is_array($data) || is_object($data)) return $data;
        if (is_string($data)) {
            $json = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) return $json;
            return @unserialize($data) ?: $data;
        }
        return $data;
    }
}
