<?php

use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\ExamDashboardController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\SubscriptionController;
use App\Http\Controllers\Student\SyllabusController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| STUDENT DASHBOARD & SYLLABUS ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // Syllabus Management
        Route::controller(SyllabusController::class)
            ->withoutMiddleware(['check.syllabus'])
            ->group(function () {
                Route::get('/change-syllabus', 'changeSyllabus')->name('change_syllabus');
                Route::post('/update-syllabus', 'updateSyllabus')->name('update_syllabus');
                Route::get('/get-current-syllabus', 'getCurrentSyllabus')->name('get_current_syllabus');
            });

        // Main Student Dashboard
        Route::controller(StudentDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/add-exams', 'addExams')->name('add_exams');
        });

        // Exam Interface Demo
        Route::get('/exam-demo', function () {
            return view('student.exam-interface');
        })->name('exam_demo');

        // Subscription Management
        Route::controller(SubscriptionController::class)->group(function () {
            Route::get('/subscriptions', 'index')->name('subscriptions.index');
            Route::post('/subscriptions/{id}/cancel', 'cancelSubscription')->name('subscriptions.cancel');
        });

        // Payment History
        Route::controller(PaymentController::class)->group(function () {
            Route::get('/payments', 'index')->name('payments.index');
            Route::get('/payments/{id}/invoice', 'downloadInvoice')->name('payments.invoice');
        });

        // --- EXAM DASHBOARD ROUTES (Complete) ---
        Route::controller(ExamDashboardController::class)->group(function () {

            // 1. Dashboard View
            Route::get('/exams', 'exam')->name('exams.dashboard');

            // 2. Exams by Type (View & Fetch)
            Route::get('/exams/type/{type:slug}', 'examsByType')->name('exams.type');
            Route::get('/exams/fetch-type/{type:slug}', 'fetchExamsByType')->name('exams.fetch_type'); // AJAX Route

            // 3. Live Exams (View & Fetch)
            Route::get('/exams/live', 'liveExams')->name('exams.live');
            Route::get('/exams/live/my', 'mySubscribedExams')->name('exams.live');
            Route::get('/exams/fetch-live', 'fetchLiveExams')->name('exams.fetch_live'); // AJAX Route
        });
        // --- DEBUG ROUTE (Delete this after fixing) ---
Route::get('/debug-exam-flow', function () {
    $user = auth()->user();

    // 1. Check User Groups (Sabse Important)
    // Exam Schedule user ke group se link hota hai. Agar user group mein nahi hai, to exam nahi dikhega.
    $userGroups = $user->userGroups()->get(['user_groups.id', 'user_groups.name']);
    $userGroupIds = $userGroups->pluck('id')->toArray();

    // 2. Check Subscriptions (Plans)
    // Dekhte hain user ne kaunse categories ke liye subscribe kiya hai.
    $activeSubscriptions = $user->subscriptions()
        ->where('status', 'active')
        ->get(['id', 'plan_id', 'category_id', 'starts_at', 'ends_at']);

    $subscribedCategoryIds = $activeSubscriptions->pluck('category_id')->unique()->values()->toArray();

    // 3. Check Current Selected Syllabus (Header wala)
    $selectedSyllabus = $user->selectedSyllabus();

    // 4. Check Available Exams based on Subscription
    // Kya database mein aise exams hain jo user ki subscribed category se match karte hain?
    $matchingExams = \App\Models\Exam::whereIn('sub_category_id', $subscribedCategoryIds)
        ->get(['id', 'title', 'sub_category_id', 'is_active']);

    // 5. Check Exam Schedules (Connections)
    // Yeh check karega ki kya koi schedule exist karta hai jo:
    // A. Un Exams ka ho jo user ke paas hain.
    // B. User ke Group ko assign kiya gaya ho.
    $matchingSchedules = \App\Models\ExamSchedule::query()
        ->whereIn('exam_id', $matchingExams->pluck('id'))
        ->with(['userGroups' => function($q) {
            $q->select('user_groups.id', 'user_groups.name');
        }])
        ->get()
        ->map(function($schedule) use ($userGroupIds) {
            // Check specific logic failure points
            $assignedGroupIds = $schedule->userGroups->pluck('id')->toArray();
            $hasCommonGroup = !empty(array_intersect($userGroupIds, $assignedGroupIds));

            return [
                'schedule_id' => $schedule->id,
                'exam_id' => $schedule->exam_id,
                'start_date' => $schedule->start_date,
                'end_date' => $schedule->end_date,
                'is_date_active' => \Carbon\Carbon::parse($schedule->end_date)->isFuture(),
                'assigned_to_groups' => $assignedGroupIds,
                'user_belongs_to_these_groups' => $hasCommonGroup ? 'YES' : 'NO (ISSUE HERE)',
            ];
        });

    return response()->json([
        '01_USER_INFO' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        '02_USER_GROUPS' => $userGroups,
        '03_ACTIVE_SUBSCRIPTIONS' => $activeSubscriptions,
        '04_SUBSCRIBED_CATEGORY_IDS' => $subscribedCategoryIds,
        '05_SELECTED_SYLLABUS' => $selectedSyllabus,
        '06_MATCHING_EXAMS_IN_DB' => $matchingExams,
        '07_EXAM_SCHEDULE_ANALYSIS' => $matchingSchedules,
    ]);
});

    });


/*
|--------------------------------------------------------------------------
| CHECKOUT & PAYMENT ROUTES (Global Access)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:guest|student|employee'])->group(function () {

    Route::controller(CheckoutController::class)->group(function () {
        Route::get('/checkout/{plan}', 'checkout')->name('checkout');
        Route::post('/checkout/{plan}', 'processCheckout')->name('process_checkout');

        Route::post('/callbacks/razorpay', 'handleRazorpayPayment')->name('razorpay_callback');
        Route::get('/payment-success', 'paymentSuccess')->name('payment_success');
        Route::get('/payment-failed', 'paymentFailed')->name('payment_failed');
    });

});
