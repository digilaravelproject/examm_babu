<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\ExamType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ExamDashboardController extends Controller
{
    /**
     * Helper: Get Subscribed Category IDs
     * Fetches fresh data directly from DB.
     */
    private function getSubscribedCategories($user)
    {
        // 1. Fetch MicroCategory IDs from active plan subscriptions
        $categoryIds = $user->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->with('plan')
            ->get()
            ->pluck('plan.category_id') // Get MicroCategory ID from Plan
            ->filter() // Remove nulls
            ->toArray();

        // 2. Add Current Selected Syllabus to list (if syllabus also uses MicroCategory)
        $currentSyllabus = $user->selectedSyllabus();
        if ($currentSyllabus) {
            $categoryIds[] = $currentSyllabus->id;
        }

        return array_unique($categoryIds);
    }

    /**
     * 1. User's Main Exam Dashboard (Grouped by Plan)
     */
    public function exam(Request $request): View
    {
        try {
            $user = $request->user();

            // --- STEP 1: Get Active Subscriptions ---
            $activeSubscriptions = $user->subscriptions()
                ->with(['plan.microCategory'])
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->get();

            // --- STEP 2: Loop through subscriptions and find exams ---
            $organizedExams = [];
            foreach ($activeSubscriptions as $subscription) {
                $schedules = ExamSchedule::query()
                    ->whereHas('exam', function (Builder $query) use ($subscription) {
                        $query->where('micro_category_id', $subscription->category_id)
                            ->where('is_active', true);
                    })
                    ->with(['exam.subCategory:id,name', 'exam.examType:id,name'])
                    ->orderBy('start_date', 'asc')
                    ->active() // Scope for active schedules
                    ->limit(8)
                    ->get();

                if ($schedules->isNotEmpty()) {
                    $organizedExams[] = [
                        'plan_name' => $subscription->plan->name ?? 'General',
                        'category_name' => $subscription->plan->microCategory->name ?? 'Exams',
                        'schedules' => $schedules
                    ];
                }
            }

            $attemptCounts = \App\Models\ExamSession::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'submitted']) // Status check karein
                ->select('exam_schedule_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('exam_schedule_id')
                ->pluck('total', 'exam_schedule_id')
                ->toArray();
            // --- STEP 3: Fetch Exam Types ---
            $examTypes = ExamType::active()->orderBy('name')->get();

            // --- STEP 4: Get IDs for logic checks ---
            $subscribedCategoryIds = $this->getSubscribedCategories($user);

            return view('student.exams.dashboard', [
                'organizedExams'        => $organizedExams,
                'examTypes'             => $examTypes,
                'subscribedCategoryIds' => $subscribedCategoryIds,
                'attemptCounts'         => $attemptCounts,
                'user'                  => $user
            ]);
        } catch (\Throwable $e) {
            Log::error("Exam Dashboard Error: " . $e->getMessage());
            abort(500, 'Unable to load dashboard.');
        }
    }
    /**
     * 6. Topic-wise Exam Dashboard
     * Fetches exams that are linked to specific topics, grouped by Topic Name.
     */
    public function examsByTopics(Request $request): View
    {
        try {
            $user = $request->user();

            // 1. Get IDs for logic checks (Security: Only show subscribed content)
            $subscribedCategoryIds = $this->getSubscribedCategories($user);

            // 2. Fetch Schedules where Exam has a Topic ID
            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($subscribedCategoryIds) {
                    $query->whereIn('micro_category_id', $subscribedCategoryIds)
                          ->where('is_active', true)
                          ->whereNotNull('topic_id'); // CRITICAL: Only exams with topics
                })
                // Eager load the Topic relationship to get the Name
                ->with(['exam.subCategory:id,name', 'exam.examType:id,name', 'exam.topic:id,name'])
                ->orderBy('start_date', 'asc')
                ->active()
                ->get();

            // 3. Group the results by Topic Name
            $groupedByTopic = $schedules->groupBy(function ($schedule) {
                return $schedule->exam->topic->name ?? 'Uncategorized Topic';
            });

            $organizedExams = [];

            // 4. Format for the View
            foreach ($groupedByTopic as $topicName => $topicSchedules) {
                $organizedExams[] = [
                    'topic_name'    => $topicName, // This is the Section Header
                    'category_name' => $topicSchedules->first()->exam->subCategory->name ?? 'General', // Just for reference
                    'schedules'     => $topicSchedules
                ];
            }

            // 5. Get Attempt Counts
            $attemptCounts = \App\Models\ExamSession::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'submitted'])
                ->select('exam_schedule_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->groupBy('exam_schedule_id')
                ->pluck('total', 'exam_schedule_id')
                ->toArray();

            return view('student.exams.topic_dashboard', [
                'organizedExams'        => $organizedExams,
                'subscribedCategoryIds' => $subscribedCategoryIds,
                'attemptCounts'         => $attemptCounts,
                'user'                  => $user
            ]);

        } catch (\Throwable $e) {
            Log::error("Topic Exam Dashboard Error: " . $e->getMessage());
            abort(500, 'Unable to load topic exams.');
        }
    }

    /**
     * 2. Live Exams List Page (Initial Load)
     */
    public function liveExams(Request $request): View
    {
        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getSubscribedCategories($user);

            // Fetch Exams
            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($visibleCategoryIds) {
                    $query->whereIn('micro_category_id', $visibleCategoryIds)
                        ->where('is_active', true);
                })
                ->with(['exam.subCategory', 'exam.examType'])
                ->orderBy('end_date', 'asc')
                ->active()
                ->paginate(9);

            return view('student.exams.live_exams', [
                'schedules'             => $schedules,
                'subscribedCategoryIds' => $visibleCategoryIds
            ]);
        } catch (\Throwable $e) {
            Log::error("Live Exams Page Error: " . $e->getMessage());
            abort(500);
        }
    }

    /**
     * 3. Fetch Live Exams (AJAX - HTML Response)
     */
    public function fetchLiveExams(Request $request): JsonResponse
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getSubscribedCategories($user);

            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($visibleCategoryIds) {
                    $query->whereIn('micro_category_id', $visibleCategoryIds)
                        ->where('is_active', true);
                })
                ->with(['exam.subCategory', 'exam.examType'])
                ->orderBy('end_date', 'asc')
                ->active()
                ->paginate(9);

            $view = view('student.exams.partials.live_exam_card', [
                'schedules'             => $schedules,
                'subscribedCategoryIds' => $visibleCategoryIds
            ])->render();

            return response()->json([
                'status'  => true,
                'html'    => $view,
                'hasMore' => $schedules->hasMorePages()
            ]);
        } catch (\Throwable $e) {
            Log::error("Fetch Live Exams Error: " . $e->getMessage());
            return response()->json(['status' => false], 500);
        }
    }

    /**
     * 4. Exams by Type Page (e.g. Mock Tests)
     */
    public function examsByType(Request $request, ExamType $type): View
    {
        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getSubscribedCategories($user);

            $exams = $type->exams()
                ->has('questions')
                ->whereIn('micro_category_id', $visibleCategoryIds)
                ->isPublic()
                ->published()
                ->with(['subCategory', 'examType'])
                ->orderBy('is_paid', 'asc')
                ->paginate(12);

            return view('student.exams.type_list', [
                'type'                  => $type,
                'exams'                 => $exams,
                'subscribedCategoryIds' => $visibleCategoryIds
            ]);
        } catch (\Throwable $e) {
            Log::error("Exams By Type Error: " . $e->getMessage());
            abort(404);
        }
    }

    /**
     * 5. Fetch Exams by Type (AJAX - HTML Response)
     */
    public function fetchExamsByType(Request $request, ExamType $type): JsonResponse
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getSubscribedCategories($user);

            $exams = $type->exams()
                ->has('questions')
                ->whereIn('micro_category_id', $visibleCategoryIds)
                ->isPublic()
                ->published()
                ->with(['subCategory', 'examType'])
                ->orderBy('is_paid', 'asc')
                ->paginate(12);

            $view = view('student.exams.partials.exam_card', [
                'exams'                 => $exams,
                'subscribedCategoryIds' => $visibleCategoryIds
            ])->render();

            return response()->json([
                'status'  => true,
                'html'    => $view,
                'hasMore' => $exams->hasMorePages()
            ]);
        } catch (\Throwable $e) {
            Log::error("Fetch Exams By Type Error: " . $e->getMessage());
            return response()->json(['status' => false], 500);
        }
    }
}
