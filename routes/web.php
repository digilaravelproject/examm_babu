<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| WEBHOOKS (Publicly Accessible)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Webhooks\RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Site Controller)
|--------------------------------------------------------------------------
*/
Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'index')->name('welcome');

    // Explore & Categories
    Route::get('/explore/{slug}', 'explore')->name('explore');
    Route::get('/categories/{slug}', 'category')->name('store.categories.show');

    // Pricing
    Route::get('/pricing/{subCategory?}', 'pricing')->name('pricing');

    // Exam Details
    Route::get('/exam_details/{subCategory}', 'exam_details')->name('exam_details.subcategory');
    Route::get('/exam_details/{subCategory}/{microCategory}', 'exam_details')->name('exam_details.microcategory');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Home & Fallback)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Home Route
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Default / Fallback Dashboard with OTP Check
    Route::get('/dashboard', function () {
        if (Auth::user() && is_null(Auth::user()->email_verified_at)) {
            return redirect()->route('otp.verify');
        }
        return view('dashboard');
    })->name('dashboard');
    Route::post('/stop-impersonation', [UserController::class, 'stopImpersonate'])->name('impersonation.stop');
});

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| INCLUDE ADDITIONAL ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/student.php';
// require __DIR__ . '/instructor.php';
require __DIR__ . '/staff.php';
