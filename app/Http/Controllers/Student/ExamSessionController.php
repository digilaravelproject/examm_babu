<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\ExamSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExamSessionController extends Controller
{
    /**
     * 1. Start Exam Logic
     */
    public function startExam(Request $request, $scheduleId)
    {
        try {
            $user = $request->user();
            $schedule = ExamSchedule::with('exam')->findOrFail($scheduleId);
            $exam = $schedule->exam;

            // A. Check for Existing Session (Resume)
            $existingSession = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->whereIn('status', ['started', 'paused'])
                ->first();

            if ($existingSession) {
                if ($existingSession->status === 'paused') {
                    $existingSession->status = 'started';
                    $existingSession->save();
                }
                return redirect()->route('student.exam.interface', $existingSession->code);
            }

            // B. Attempt Limit Check
            $maxAttempts = $exam->settings['no_of_attempts'] ?? 0;
            $attemptsCount = ExamSession::where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->where('status', 'completed')
                ->count();

            if ($maxAttempts > 0 && $attemptsCount >= $maxAttempts) {
                return redirect()->back()->with('error', 'Maximum attempts reached.');
            }

            // C. Subscription & Wallet Check
            $hasSubscription = $user->hasActiveSubscription($exam->sub_category_id, 'exams');

            if ($exam->is_paid && !$hasSubscription) {
                if ($exam->can_redeem && $user->balance >= $exam->points_required) {
                    $user->withdraw($exam->points_required, [
                        'description' => 'Unlocked Exam: ' . $exam->title
                    ]);
                } else {
                    return redirect()->route('pricing')->with('error', 'Please subscribe or recharge wallet.');
                }
            }

            // D. Create New Session
            $session = new ExamSession();
            $session->code = 'SESS-' . strtoupper(Str::random(10));
            $session->user_id = $user->id;
            $session->exam_id = $exam->id;
            $session->exam_schedule_id = $schedule->id;
            $session->status = 'started';

            // TIME FIX
            $session->starts_at = now();

            if ($schedule->schedule_type == 'fixed') {
                $session->ends_at = $schedule->end_date;
            } else {
                $session->ends_at = now()->addMinutes($exam->duration ?? 60);
            }
            $session->save();

            return redirect()->route('student.exam.interface', $session->code);

        } catch (\Throwable $e) {
            Log::error("Start Exam Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to start exam.');
        }
    }

    /**
     * 2. Load Interface (View)
     */
    public function loadInterface($sessionCode)
    {
        try {
            $session = ExamSession::where('code', $sessionCode)
                ->with(['exam.examSections'])
                ->firstOrFail();

            if ($session->user_id !== Auth::id()) abort(403);

            // Time Check
            $remainingSeconds = now()->diffInSeconds($session->ends_at, false);
            if ($remainingSeconds <= 0 || $session->status === 'completed') {
                return redirect()->route('student.exams.result', $session->id);
            }

            // ORDER FIX: Using 'section_order' as per your database
            $sections = $session->exam->examSections()
                ->orderBy('section_order')
                ->get(['id', 'name', 'total_questions'])
                ->map(function($sec) {
                    return [
                        'id' => $sec->id,
                        'name' => $sec->name,
                        'total_questions' => $sec->total_questions,
                        'questions' => []
                    ];
                });

            return view('student.exams.interface', [
                'session' => $session,
                'exam' => $session->exam,
                'sections' => $sections,
                'remainingSeconds' => $remainingSeconds,
                'user' => Auth::user()
            ]);

        } catch (\Throwable $e) {
            Log::error("Load Interface Error: " . $e->getMessage());
            abort(500);
        }
    }

    /**
     * 3. Fetch Questions (AJAX) - DB QUERY FIX
     */
    public function fetchSectionQuestions($sessionCode, $sectionId)
    {
        try {
            $session = ExamSession::where('code', $sessionCode)->firstOrFail();

            // --- FIX: Using DB Table directly instead of missing Model Relation ---
            // This assumes your pivot table is 'exam_section_questions'
            // If fetching fails, check your actual pivot table name in DB
            $questionIds = DB::table('exam_section_questions')
                ->where('exam_section_id', $sectionId)
                ->orderBy('sno', 'asc') // Assuming 'sno' column exists for ordering
                ->pluck('question_id');

            $questions = Question::whereIn('id', $questionIds)->get();
            // ---------------------------------------------------------------------

            // Get saved answers
            $savedAnswers = DB::table('exam_session_questions')
                ->where('exam_session_id', $session->id)
                ->whereIn('question_id', $questions->pluck('id'))
                ->get()
                ->keyBy('question_id');

            $data = $questions->map(function($q) use ($savedAnswers) {
                $saved = $savedAnswers->get($q->id);

                // Handle Options (Array/JSON fix)
                $options = $q->options;
                if (is_string($options)) {
                    $options = json_decode($options, true) ?? [];
                }

                return [
                    'id' => $q->id,
                    'text' => $q->question,
                    'options' => $options,
                    'marks' => $q->default_marks,
                    'negative' => $q->default_negative_marks ?? 0,
                    'status' => $saved ? $saved->status : 'not_visited',
                    'selected_option' => $saved ? unserialize($saved->user_answer) : null,
                ];
            });

            return response()->json(['questions' => $data]);

        } catch (\Throwable $e) {
            Log::error("Fetch Questions Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load questions'], 500);
        }
    }

    /**
     * 4. Save Answer (Real-time Marking)
     */
    public function saveAnswer(Request $request, $sessionCode)
    {
        try {
            $session = ExamSession::where('code', $sessionCode)->firstOrFail();
            $question = Question::find($request->question_id);

            // Evaluation Logic
            $isCorrect = false;
            if ($request->selected_option !== null) {
                $isCorrect = ($question->correct_answer == $request->selected_option);
            }

            $marksEarned = $isCorrect ? $question->default_marks : 0;
            $marksDeducted = (!$isCorrect && $request->selected_option !== null)
                ? ($question->default_negative_marks ?? 0)
                : 0;

            DB::table('exam_session_questions')->upsert([
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
                'exam_section_id' => $request->section_id,
                'user_answer' => serialize($request->selected_option),
                'status' => $request->status,
                'is_correct' => $isCorrect,
                'marks_earned' => $marksEarned,
                'marks_deducted' => $marksDeducted,
                'time_taken' => $request->time_taken ?? 0,
                'updated_at' => now()
            ], ['exam_session_id', 'question_id'], ['user_answer', 'status', 'is_correct', 'marks_earned', 'marks_deducted', 'updated_at']);

            $session->current_section = $request->section_id;
            $session->current_question = $request->question_id;
            $session->save();

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error("Save Answer Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to save'], 500);
        }
    }

    /**
     * 5. Suspend Session
     */
    public function suspendSession($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();
        $session->status = 'paused';
        $session->save();
        return response()->json(['redirect' => route('student.exams.dashboard')]);
    }

    /**
     * 6. Finish Exam
     */
    public function finishExam($sessionCode)
    {
        $session = ExamSession::where('code', $sessionCode)->firstOrFail();
        $session->status = 'completed';
        $session->completed_at = now();
        $session->save();

        return response()->json(['redirect' => route('student.exams.result', $session->id)]);
    }

    public function showResult($sessionId) {
        // Placeholder
        return "Result Page for Session ID: " . $sessionId;
    }
}
