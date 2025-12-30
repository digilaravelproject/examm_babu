<?php

use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\StudentDashboardController;
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
