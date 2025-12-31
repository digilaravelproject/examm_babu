<?php

use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\ExamDashboardController;
use App\Http\Controllers\Student\ExamSessionController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\SubscriptionController;
use App\Http\Controllers\Student\SyllabusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| STUDENT DASHBOARD & FEATURE ROUTES
|--------------------------------------------------------------------------
| Prefix: /student
| Name Prefix: student.
*/

Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // ====================================================
        // 1. SYLLABUS MANAGEMENT
        // ====================================================
        Route::controller(SyllabusController::class)
            ->withoutMiddleware(['check.syllabus'])
            ->group(function () {
                Route::get('/change-syllabus', 'changeSyllabus')->name('change_syllabus');
                Route::post('/update-syllabus', 'updateSyllabus')->name('update_syllabus');
                Route::get('/get-current-syllabus', 'getCurrentSyllabus')->name('get_current_syllabus');
            });

        // ====================================================
        // 2. DASHBOARD OVERVIEW
        // ====================================================
        Route::controller(StudentDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/add-exams', 'addExams')->name('add_exams');
        });

        // ====================================================
        // 3. EXAM DASHBOARD & LISTINGS
        // ====================================================
        Route::controller(ExamDashboardController::class)->group(function () {

            // Main Exam Dashboard
            Route::get('/exams', 'exam')->name('exams.dashboard');

            // Exams by Type (e.g., Mock Test, Practice)
            Route::get('/exams/type/{type:slug}', 'examsByType')->name('exams.type');
            Route::get('/exams/fetch-type/{type:slug}', 'fetchExamsByType')->name('exams.fetch_type'); // AJAX

            // Live Exams
            Route::get('/exams/live', 'liveExams')->name('exams.live');
            Route::get('/exams/fetch-live', 'fetchLiveExams')->name('exams.fetch_live'); // AJAX
        });
        // --- EXAM SESSION ENGINE ---
        Route::controller(ExamSessionController::class)->group(function () {

            // 1. Start Exam (Logic: Checks Subscription -> Wallet -> Limits)
            Route::get('/exam/start/{scheduleId}', 'startExam')->name('exam.start');

            // 2. Exam Interface (Main Screen)
            Route::get('/exam/attempt/{sessionCode}', 'loadInterface')->name('exam.interface');

            // 3. Fetch Questions (AJAX - Section Wise)
            Route::get('/exam/fetch-section/{sessionCode}/{sectionId}', 'fetchSectionQuestions')->name('exam.fetch_section');

            // 4. Save Answer (AJAX - Realtime)
            Route::post('/exam/save-answer/{sessionCode}', 'saveAnswer')->name('exam.save_answer');

            // 5. Suspend (Tab Switching Penalty)
            Route::post('/exam/suspend/{sessionCode}', 'suspendSession')->name('exam.suspend');

            // 6. Finish Exam
            Route::post('/exam/finish/{sessionCode}', 'finishExam')->name('exam.finish');

            // 7. Results & Solutions
            Route::get('/exam/result/{sessionId}', 'showResult')->name('exams.result');
        });
        // Demo Interface
        Route::get('/exam-demo', function () {
            return view('student.exam-interface');
        })->name('exam_demo');

        // ====================================================
        // 4. BILLING & SUBSCRIPTIONS
        // ====================================================

        // Subscriptions
        Route::controller(SubscriptionController::class)->group(function () {
            Route::get('/subscriptions', 'index')->name('subscriptions.index');
            Route::post('/subscriptions/{id}/cancel', 'cancelSubscription')->name('subscriptions.cancel');
        });

        // Payment History & Invoices
        Route::controller(PaymentController::class)->group(function () {
            Route::get('/payments', 'index')->name('payments.index');
            Route::get('/payments/{id}/invoice', 'downloadInvoice')->name('payments.invoice');
        });

    });

/*
|--------------------------------------------------------------------------
| GLOBAL CHECKOUT ROUTES
|--------------------------------------------------------------------------
| Accessible by: Guest, Student, Employee
| Note: No 'student.' prefix here to keep URLs clean (e.g., /checkout/plan-123)
*/

Route::middleware(['auth', 'verified', 'role:guest|student|employee'])->group(function () {

    Route::controller(CheckoutController::class)->group(function () {
        // Checkout Pages
        Route::get('/checkout/{plan}', 'checkout')->name('checkout');
        Route::post('/checkout/{plan}', 'processCheckout')->name('process_checkout');

        // Payment Status & Callbacks
        Route::post('/callbacks/razorpay', 'handleRazorpayPayment')->name('razorpay_callback');
        Route::get('/payment-success', 'paymentSuccess')->name('payment_success');
        Route::get('/payment-failed', 'paymentFailed')->name('payment_failed');
    });

});
