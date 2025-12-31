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
     * 1. User's Main Exam Dashboard
     */
    public function exam(Request $request): View
    {
        try {
            $user = $request->user();
            $currentSyllabus = $user->selectedSyllabus();

            // 1. Get IDs of Categories user has SUBSCRIBED to (Active Plans)
            $subscribedCategoryIds = $user->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->pluck('category_id')
                ->toArray();

            // 2. Add Current Syllabus ID (From Header) to visibility list
            $visibleCategoryIds = $subscribedCategoryIds;
            if ($currentSyllabus) {
                $visibleCategoryIds[] = $currentSyllabus->id;
            }
            $visibleCategoryIds = array_unique($visibleCategoryIds);

            // 3. Fetch Schedules (FIXED LOGIC: Removed Strict User Group Check)
            // Logic: Show exams if they belong to a category the user has access to.
            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($visibleCategoryIds) {
                    $query->whereIn('sub_category_id', $visibleCategoryIds)
                          ->where('is_active', true);
                })
                ->with(['exam.subCategory', 'exam.examType'])
                ->orderBy('start_date', 'asc') // Upcoming first
                ->active() // Must be currently active dates
                ->limit(4)
                ->get();

            // Fetch Exam Types
            $examTypes = ExamType::active()->orderBy('name')->get();

            return view('student.exams.dashboard', [
                'examSchedules' => $schedules,
                'examTypes'     => $examTypes,
                'subscription'  => $user->hasActiveSubscription($currentSyllabus->id ?? 0, 'exams'),
                'category'      => $currentSyllabus
            ]);

        } catch (\Throwable $e) {
            Log::error("Exam Dashboard Error: " . $e->getMessage());
            return view('student.exams.dashboard', [
                'examSchedules' => collect(), 'examTypes' => collect(), 'subscription' => false, 'category' => null
            ]);
        }
    }

    /**
     * 2. Live Exams List Page
     */
    public function liveExams(Request $request): View
    {
        try {
            $user = $request->user();
            $currentSyllabus = $user->selectedSyllabus();

            // Get Subscribed + Selected Categories
            $subscribedCategoryIds = $user->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->pluck('category_id')
                ->toArray();

            $visibleCategoryIds = $subscribedCategoryIds;
            if ($currentSyllabus) {
                $visibleCategoryIds[] = $currentSyllabus->id;
            }
            $visibleCategoryIds = array_unique($visibleCategoryIds);

            // Fetch Exams (User Group Check Removed for Subscribed Users)
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
                'schedules'    => $schedules,
                'subscription' => $user->hasActiveSubscription($currentSyllabus->id ?? 0, 'exams')
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
            $currentSyllabus = $user->selectedSyllabus();

            $subscribedCategoryIds = $user->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->pluck('category_id')
                ->toArray();

            $visibleCategoryIds = $subscribedCategoryIds;
            if ($currentSyllabus) { $visibleCategoryIds[] = $currentSyllabus->id; }
            $visibleCategoryIds = array_unique($visibleCategoryIds);

            // FIXED QUERY
            $schedules = ExamSchedule::query()
                ->whereHas('exam', function (Builder $query) use ($visibleCategoryIds) {
                    $query->whereIn('sub_category_id', $visibleCategoryIds)
                          ->where('is_active', true);
                })
                ->with(['exam.subCategory', 'exam.examType'])
                ->orderBy('end_date', 'asc')
                ->active()
                ->paginate(9);

            $subscription = $user->hasActiveSubscription($currentSyllabus->id ?? 0, 'exams');

            // Render Partial View to append in grid
            $view = view('student.exams.partials.live_exam_card', compact('schedules', 'subscription'))->render();

            return response()->json([
                'status' => true,
                'html' => $view,
                'hasMore' => $schedules->hasMorePages()
            ]);

        } catch (\Throwable $e) {
            Log::error("Fetch Live Exams Error: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error loading data'], 500);
        }
    }

    /**
     * 4. Exams by Type Page (e.g. Mock Tests)
     */
    public function examsByType(Request $request, ExamType $type): View
    {
        try {
            $user = $request->user();
            $category = $user->selectedSyllabus();

            // Determine visible categories (Subscribed + Selected)
            $subscribedCategoryIds = $user->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->pluck('category_id')
                ->toArray();

            if ($category) { $subscribedCategoryIds[] = $category->id; }
            $visibleCategoryIds = array_unique($subscribedCategoryIds);

            $exams = $type->exams()
                ->has('questions')
                ->whereIn('sub_category_id', $visibleCategoryIds) // <--- Use array here
                ->isPublic()
                ->published()
                ->with(['subCategory', 'examType'])
                ->orderBy('is_paid', 'asc')
                ->paginate(12);

            return view('student.exams.type_list', [
                'type'         => $type,
                'exams'        => $exams,
                'subscription' => $user->hasActiveSubscription($category->id ?? 0, 'exams')
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
            $category = $user->selectedSyllabus();

             $subscribedCategoryIds = $user->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->pluck('category_id')
                ->toArray();

            if ($category) { $subscribedCategoryIds[] = $category->id; }
            $visibleCategoryIds = array_unique($subscribedCategoryIds);

            $exams = $type->exams()
                ->has('questions')
                ->whereIn('sub_category_id', $visibleCategoryIds)
                ->isPublic()
                ->published()
                ->with(['subCategory', 'examType'])
                ->orderBy('is_paid', 'asc')
                ->paginate(12);

            $subscription = $user->hasActiveSubscription($category->id ?? 0, 'exams');

            // Assuming reuse of similar partial or you create 'exam_card.blade.php'
            $view = view('student.exams.partials.exam_card', compact('exams', 'subscription'))->render();

            return response()->json([
                'status' => true,
                'html' => $view,
                'hasMore' => $exams->hasMorePages()
            ]);

        } catch (\Throwable $e) {
            Log::error("Fetch Exams By Type Error: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error loading data'], 500);
        }
    }
}
