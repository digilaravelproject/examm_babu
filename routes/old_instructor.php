<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instructor\ReferralController;
use App\Http\Controllers\Instructor\WithdrawalController;
// Note: QuestionController is often in Admin namespace, ensuring correct usage here
use App\Http\Controllers\Admin\QuestionController;

/*
|--------------------------------------------------------------------------
| INSTRUCTOR ROUTES (Prefix: /instructor)
|--------------------------------------------------------------------------
*/

// --- GROUP 1: General Instructor Dashboard & Actions ---
Route::middleware(['auth', 'verified', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            // Merged logic: Returns the instructor specific view
            return view('instructor.dashboard');
        })->name('dashboard');

        // Custom Question Actions (Preview, Update Steps)
        Route::controller(QuestionController::class)->prefix('questions')->name('questions.')->group(function () {
            Route::get('/{question}/preview', 'preview')->name('preview');
            Route::put('/{question}/settings', 'updateSettings')->name('update_settings');
            Route::put('/{question}/solution', 'updateSolution')->name('update_solution');
            Route::put('/{question}/attachment', 'updateAttachment')->name('update_attachment');
        });

        // Standard Resource for Questions
        Route::resource('questions', QuestionController::class);
    });


// --- GROUP 2: Referral & Earnings (Permission Based) ---
// Note: Middleware 'can:access referral' ensures only permitted users access this
Route::middleware(['auth', 'verified', 'can:access referral'])->group(function () {

    // Dashboard Route
    Route::get('/refer-and-earn', [ReferralController::class, 'index'])
        ->name('referral.dashboard');

    // Withdrawal Request Route
    Route::post('/payout/request', [WithdrawalController::class, 'store'])
        ->name('referral.withdraw.store');
});
