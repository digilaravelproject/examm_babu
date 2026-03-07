<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\ExamDashboardController;
use App\Http\Controllers\Student\ExamSessionController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\ShareReportController; // ✅ New Controller
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\SubscriptionController;
use App\Http\Controllers\Student\SyllabusController;

/*
|--------------------------------------------------------------------------
| STUDENT PORTAL ROUTES
|--------------------------------------------------------------------------
*/

// --- GROUP 1: Student Dashboard & Features (Strictly Student Only) ---
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {

    // Syllabus Management
    Route::controller(SyllabusController::class)->withoutMiddleware(['check.syllabus'])->group(function () {
        Route::get('/change-syllabus', 'changeSyllabus')->name('change_syllabus');
        Route::post('/update-syllabus', 'updateSyllabus')->name('update_syllabus');
        Route::get('/get-current-syllabus', 'getCurrentSyllabus')->name('get_current_syllabus');
    });

    // Dashboard Overview
    Route::controller(StudentDashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/add-exams', 'addExams')->name('add_exams');
    });

    // Exam Listings & Types
    Route::controller(ExamDashboardController::class)->group(function () {
        Route::get('/exams', 'exam')->name('exams.dashboard');
        Route::get('/exams/type/{type:slug}', 'examsByType')->name('exams.type');
        Route::get('/exams/fetch-type/{type:slug}', 'fetchExamsByType')->name('exams.fetch_type');
        Route::get('/exams/live', 'liveExams')->name('exams.live');
        Route::get('/exams/fetch-live', 'fetchLiveExams')->name('exams.fetch_live');
        Route::get('/exams/topics', 'examsByTopics')->name('exams.topic-wise');
    });

    // Subscriptions
    Route::controller(SubscriptionController::class)->group(function () {
        Route::get('/subscriptions', 'index')->name('subscriptions.index');
        Route::post('/subscriptions/{id}/cancel', 'cancelSubscription')->name('subscriptions.cancel');
    });

    // Payments & History
    Route::controller(PaymentController::class)->group(function () {
        Route::get('/payments', 'index')->name('payments.index');
        Route::get('/payments/invoice/{paymentId}', 'previewInvoice')->name('payments.invoice.preview');
        Route::get('/payments/invoice/{paymentId}/download', 'downloadInvoice')->name('payments.invoice.download');
    });
});

// --- GROUP 2: Exam Interface Engine (Student & Admin) ---
Route::middleware(['auth', 'verified', 'role:student|admin'])->prefix('student')->name('student.')->group(function () {

    Route::controller(ExamSessionController::class)->group(function () {
        // Start & Interface
        Route::get('/exam/start/{scheduleId}', 'startExam')->name('exam.start');
        Route::get('/exam/attempt/{sessionCode}', 'loadInterface')->name('exam.interface');

        // AJAX: Questions & Answers
        Route::get('/exam/fetch-section/{sessionCode}/{sectionId}', 'fetchSectionQuestions')->name('exam.fetch_section');
        Route::post('/exam/save-answer/{sessionCode}', 'saveAnswer')->name('exam.save_answer');

        // Finish & Result
        Route::post('/exam/terminate/{sessionCode}', 'terminateExam')->name('exam.terminate');
        Route::post('/exam/finish/{sessionCode}', 'finishExam')->name('exam.finish');
        Route::get('/exam/result/{sessionId}', 'showResult')->name('exams.result');
        Route::get('/exam/review/{sessionId}', 'showReview')->name('exams.review'); // ✅ New Route for Test Review

        // ✅ SHARE REPORT (Sending Email requires Auth) - Moved here for Admin Access too
        Route::post('/exam/share/send/{sessionCode}', [ShareReportController::class, 'sendShareLink'])->name('exam.share.send');
    });

    // Demo Interface
    Route::get('/exam-demo', function () {
        return view('student.exam-interface');
    })->name('exam_demo');
});

// ✅ PUBLIC REPORT (Outside Auth, Secured by Signed URL)
Route::get('/exam/public-report/{sessionCode}', [ShareReportController::class, 'viewPublicReport'])
     ->name('exam.share.public_view')
     ->middleware('signed');

/*
|--------------------------------------------------------------------------
| GLOBAL CHECKOUT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:guest|student|employee'])->group(function () {
    Route::controller(CheckoutController::class)->group(function () {
        Route::get('/checkout/{plan}', 'checkout')->name('checkout');
        Route::post('/checkout/{plan}', 'processCheckout')->name('process_checkout');

        // Callbacks
        Route::post('/callbacks/razorpay', 'handleRazorpayPayment')->name('razorpay_callback');
        Route::get('/payment-success', 'paymentSuccess')->name('payment_success');
        Route::get('/payment-failed', 'paymentFailed')->name('payment_failed');
    });
});
