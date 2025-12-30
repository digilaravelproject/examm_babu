<?php

use App\Http\Controllers\Student\CheckoutController;
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

        Route::controller(SyllabusController::class)
            ->withoutMiddleware(['check.syllabus'])
            ->group(function () {
                Route::get('/change-syllabus', 'changeSyllabus')->name('change_syllabus');
                Route::post('/update-syllabus', 'updateSyllabus')->name('update_syllabus');
                Route::get('/get-current-syllabus', 'getCurrentSyllabus')->name('get_current_syllabus');
            });

        Route::controller(StudentDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/add-exams', 'addExams')->name('add_exams');
        });

        Route::get('/exam-demo', function () {
            return view('student.exam-interface');
        })->name('exam_demo');
         Route::controller(SubscriptionController::class)->group(function () {
            Route::get('/subscriptions', 'index')->name('subscriptions.index');
            Route::post('/subscriptions/{id}/cancel', 'cancelSubscription')->name('subscriptions.cancel');
        });
        Route::controller(PaymentController::class)->group(function () {
            Route::get('/payments', 'index')->name('payments.index');
            Route::get('/payments/{id}/invoice', 'downloadInvoice')->name('payments.invoice');
        });
    });

/*
|--------------------------------------------------------------------------
| CHECKOUT & PAYMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:guest|student|employee'])->group(function () {

    // Controller Grouping for cleaner code
    Route::controller(CheckoutController::class)->group(function () {
        // Parameter name '{plan}' must match Controller variable
        Route::get('/checkout/{plan}', 'checkout')->name('checkout');
        Route::post('/checkout/{plan}', 'processCheckout')->name('process_checkout');

        // Callbacks & Status
        Route::post('/callbacks/razorpay', 'handleRazorpayPayment')->name('razorpay_callback');
        Route::get('/payment-success', 'paymentSuccess')->name('payment_success');
        Route::get('/payment-failed', 'paymentFailed')->name('payment_failed');
    });

});
