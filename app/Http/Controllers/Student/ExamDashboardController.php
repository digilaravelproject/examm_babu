<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\ExamType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ExamDashboardController extends Controller
{
    /**
     * Helper: Get Cached Subscribed Category IDs
     * Prevents querying the subscription table repeatedly.
     */
    private function getCachedSubscribedCategories($user)
    {
        return Cache::remember("user_{$user->id}_subscribed_cats", now()->addMinutes(10), function () use ($user) {
            $categoryIds = $user->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->pluck('category_id')
                ->toArray();

            // Add Current Selected Syllabus to list
            $currentSyllabus = $user->selectedSyllabus();
            if ($currentSyllabus) {
                $categoryIds[] = $currentSyllabus->id;
            }

            return array_unique($categoryIds);
        });
    }

    /**
     * 1. User's Main Exam Dashboard (Grouped by Plan)
     */
    public function exam(Request $request): View
    {
        try {
            $user = $request->user();

            // Cache Key for Dashboard Data (Unique per user)
            $cacheKey = "user_{$user->id}_dashboard_data";

            // Cache Dashboard Data for 30 Minutes
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user) {

                // 1. Get Active Subscriptions with Relations
                $activeSubscriptions = $user->subscriptions()
                    ->with(['plan.category'])
                    ->where('status', 'active')
                    ->where('ends_at', '>', now())
                    ->get();

                $organizedExams = [];

                foreach ($activeSubscriptions as $subscription) {
                    $schedules = ExamSchedule::query()
                        ->whereHas('exam', function (Builder $query) use ($subscription) {
                            $query->where('sub_category_id', $subscription->category_id)
                                  ->where('is_active', true);
                        })
                        ->with(['exam.subCategory:id,name', 'exam.examType:id,name']) // Optimized Select
                        ->orderBy('start_date', 'asc')
                        ->active()
                        ->limit(8)
                        ->get();

                    if ($schedules->isNotEmpty()) {
                        $organizedExams[] = [
                            'plan_name' => $subscription->plan->name ?? 'General',
                            'category_name' => $subscription->plan->category->name ?? 'Exams',
                            'schedules' => $schedules
                        ];
                    }
                }

                // 2. Fetch Exam Types (Global Cache)
                $examTypes = Cache::remember('all_active_exam_types', now()->addDay(), function () {
                    return ExamType::active()->orderBy('name')->get();
                });

                return [
                    'organizedExams' => $organizedExams,
                    'examTypes' => $examTypes
                ];
            });

            // Get Subscribed IDs for "Start/Unlock" logic (Fast Cache)
            $subscribedCategoryIds = $this->getCachedSubscribedCategories($user);

            return view('student.exams.dashboard', [
                'organizedExams' => $data['organizedExams'],
                'examTypes'      => $data['examTypes'],
                'subscribedCategoryIds' => $subscribedCategoryIds,
                'user' => $user
            ]);

        } catch (\Throwable $e) {
            Log::error("Exam Dashboard Error: " . $e->getMessage());
            abort(500, 'Unable to load dashboard.');
        }
    }

    /**
     * 2. Live Exams List Page (Initial Load)
     */
    public function liveExams(Request $request): View
    {
        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getCachedSubscribedCategories($user);

            // Fetch Exams
            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($visibleCategoryIds) {
                    $query->whereIn('sub_category_id', $visibleCategoryIds)
                          ->where('is_active', true);
                })
                ->with(['exam.subCategory', 'exam.examType'])
                ->orderBy('end_date', 'asc')
                ->active()
                ->paginate(9);

            return view('student.exams.live_exams', [
                'schedules' => $schedules,
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
        if (!$request->ajax()) { abort(404); }

        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getCachedSubscribedCategories($user);

            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($visibleCategoryIds) {
                    $query->whereIn('sub_category_id', $visibleCategoryIds)
                          ->where('is_active', true);
                })
                ->with(['exam.subCategory', 'exam.examType'])
                ->orderBy('end_date', 'asc')
                ->active()
                ->paginate(9);

            $view = view('student.exams.partials.live_exam_card', [
                'schedules' => $schedules,
                'subscribedCategoryIds' => $visibleCategoryIds
            ])->render();

            return response()->json([
                'status' => true,
                'html' => $view,
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
            $visibleCategoryIds = $this->getCachedSubscribedCategories($user);

            $exams = $type->exams()
                ->has('questions')
                ->whereIn('sub_category_id', $visibleCategoryIds)
                ->isPublic()
                ->published()
                ->with(['subCategory', 'examType'])
                ->orderBy('is_paid', 'asc')
                ->paginate(12);

            return view('student.exams.type_list', [
                'type' => $type,
                'exams' => $exams,
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
        if (!$request->ajax()) { abort(404); }

        try {
            $user = $request->user();
            $visibleCategoryIds = $this->getCachedSubscribedCategories($user);

            $exams = $type->exams()
                ->has('questions')
                ->whereIn('sub_category_id', $visibleCategoryIds)
                ->isPublic()
                ->published()
                ->with(['subCategory', 'examType'])
                ->orderBy('is_paid', 'asc')
                ->paginate(12);

            $view = view('student.exams.partials.exam_card', [
                'exams' => $exams,
                'subscribedCategoryIds' => $visibleCategoryIds
            ])->render();

            return response()->json([
                'status' => true,
                'html' => $view,
                'hasMore' => $exams->hasMorePages()
            ]);

        } catch (\Throwable $e) {
            Log::error("Fetch Exams By Type Error: " . $e->getMessage());
            return response()->json(['status' => false], 500);
        }
    }
}
