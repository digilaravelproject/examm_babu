<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\ExamSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserExamRepository
{
    /**
     * Verify if user can access this schedule
     */
    public function checkAccess(ExamSchedule $schedule, $user)
    {
        $now = now();
        $allowAccess = false;

        // 1. Check Time Window
        if ($schedule->schedule_type == 'fixed') {
            $graceEnd = $schedule->starts_at->copy()->addMinutes($schedule->grace_period ?? 0);
            $allowAccess = $now->between($schedule->starts_at, $graceEnd);
        } elseif ($schedule->schedule_type == 'flexible') {
            $allowAccess = $now->between($schedule->starts_at, $schedule->ends_at);
        }

        if (!$allowAccess) {
            return ['allowed' => false, 'message' => __('schedule_close_note')];
        }

        // 2. Check Subscription
        $hasSubscription = $user->hasActiveSubscription($schedule->exam->sub_category_id, 'exams');

        if ($schedule->exam->is_paid && !$hasSubscription) {
             return ['allowed' => false, 'message' => __('You need an active plan to access this exam.')];
        }

        return ['allowed' => true];
    }

    /**
     * Create a Session with SNAPSHOTS and SHUFFLING
     */
    public function createSession(Exam $exam, ExamSchedule $schedule, $user)
    {
        return DB::transaction(function () use ($exam, $schedule, $user) {
            $now = now();

            // Calculate End Time
            if ($schedule->schedule_type == 'fixed') {
                $endsAt = $schedule->ends_at;
            } else {
                $endsAt = $now->copy()->addSeconds($exam->total_duration);
            }

            // 1. Create the Session Record
            $session = ExamSession::create([
                'code' => 'SESS-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'exam_schedule_id' => $schedule->id,
                'status' => 'started',
                'starts_at' => $now,
                'ends_at' => $endsAt,
                'total_time_taken' => 0,
            ]);

            // 2. Prepare Sections & Questions
            $sections = $exam->examSections()->orderBy('section_order', 'asc')->get();

            // Group Questions by Section ID
            $examQuestions = $exam->questions()
                ->with('questionType')
                ->get()
                ->groupBy('pivot.exam_section_id');

            $sessionQuestionsData = [];
            $sessionSectionsData = [];

            $globalSno = 1;
            $currentSectionStart = $now->copy();

            foreach ($sections as $section) {
                // Determine Section Timings (Logic from Old Code)
                // If section duration is auto, we sum it up, otherwise assume logic
                $sectionEndTime = $currentSectionStart->copy()->addSeconds($section->total_duration);

                // --- FIX: Add all required columns from Old Code logic ---
                $sessionSectionsData[] = [
                    'exam_session_id' => $session->id,
                    'exam_section_id' => $section->id,
                    'section_id'      => $section->section_id, // Important: The reference ID
                    'name'            => $section->name,       // FIXED: Missing Name
                    'sno'             => $section->section_order,
                    'status'          => ($globalSno == 1) ? 'started' : 'not_visited',
                    'starts_at'       => $currentSectionStart->toDateTimeString(),
                    'ends_at'         => $sectionEndTime->toDateTimeString(),
                    'total_time_taken'=> 0,
                    'current_question'=> 0, // Default value
                    'results'         => null
                ];

                // Prepare Questions for this Section
                if (isset($examQuestions[$section->id])) {
                    $qList = $examQuestions[$section->id];

                    // SHUFFLING LOGIC
                    if ($exam->settings['shuffle_questions'] ?? false) {
                        $qList = $qList->shuffle();
                    }

                    $sectionQNo = 1;
                    foreach ($qList as $q) {
                        // Decode Options/Answers for Snapshot
                        // In old code: formatQuestionProperty & formatOptionsProperty
                        // Here we store raw, and handle format on frontend fetch

                        $sessionQuestionsData[] = [
                            'exam_session_id' => $session->id,
                            'question_id'     => $q->id,
                            'exam_section_id' => $section->id,
                            'sno'             => $sectionQNo++, // Order in this section
                            'original_question' => $q->question,
                            'options'         => is_array($q->options) ? json_encode($q->options) : $q->options,
                            'correct_answer'  => is_array($q->correct_answer) ? serialize($q->correct_answer) : $q->correct_answer,
                            'user_answer'     => null,
                            'status'          => 'not_visited',
                            'is_correct'      => 0,
                            'time_taken'      => 0,
                            'marks_earned'    => 0,
                            'marks_deducted'  => 0,
                            // NO TIMESTAMPS: Your table definition image shows no created_at/updated_at
                        ];
                        $globalSno++;
                    }
                }

                // Set start time for next section
                $currentSectionStart = $sectionEndTime;
            }

            // 3. Bulk Insert
            if (!empty($sessionSectionsData)) {
                DB::table('exam_session_sections')->insert($sessionSectionsData);
            }

            if (!empty($sessionQuestionsData)) {
                DB::table('exam_session_questions')->insert($sessionQuestionsData);
            }

            return $session;
        });
    }

    /**
     * Evaluate Answer
     */
    public function evaluateAnswer(Question $question, $userAnswer)
    {
        $correctAnswer = $question->correct_answer;
        $typeCode = $question->questionType->code ?? 'MSA';

        switch ($typeCode) {
            case 'MMA':
                if (!is_array($userAnswer)) return false;
                $correctArr = is_string($correctAnswer) ? json_decode($correctAnswer, true) : $correctAnswer;
                if(!is_array($correctArr)) $correctArr = explode(',', $correctArr);

                // Sort both arrays to compare contents regardless of order
                $u = $userAnswer; $c = $correctArr;
                sort($u); sort($c);
                return $u == $c;

            case 'FIB':
                return strtolower(trim((string)$userAnswer)) === strtolower(trim((string)$correctAnswer));

            case 'MTF':
            case 'ORD':
                return $userAnswer === $correctAnswer;

            case 'MSA':
            case 'TOF':
            default:
                return (string)$userAnswer === (string)$correctAnswer;
        }
    }

    /**
     * Calculate Marks
     */
    public function calculateMarks(Exam $exam, ExamSection $section, Question $question, bool $isCorrect)
    {
        $autoGrading = $exam->settings['auto_grading'] ?? true;

        $earned = 0;
        $deducted = 0;

        if ($isCorrect) {
            $earned = $autoGrading ? $question->default_marks : $section->correct_marks;
        } else {
            if ($exam->settings['enable_negative_marking'] ?? false) {
                $negativeType = $section->negative_marking_type ?? 'fixed';
                $baseMarks = $autoGrading ? $question->default_marks : $section->correct_marks;

                if ($negativeType == 'percentage') {
                    $deducted = ($baseMarks * ($section->negative_marks ?? 0)) / 100;
                } else {
                    $deducted = $section->negative_marks ?? 0;
                }
            }
        }

        return ['earned' => $earned, 'deducted' => $deducted];
    }
}
