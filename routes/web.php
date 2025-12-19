<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\StudentDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| General Authenticated Routes (Home & Fallback Dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Home Route
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Default / Fallback Dashboard
    // Agar user ke paas koi specific dashboard nahi hai, to wo yahan aayega.
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
| Prefix: /student
| Name: student.*
*/
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::get('/exam-demo', function () {
            return view('student.exam-interface');
        })->name('exam_demo');

    });

/*
|--------------------------------------------------------------------------
| INSTRUCTOR ROUTES
|--------------------------------------------------------------------------
| Prefix: /instructor
| Name: instructor.*
*/
Route::middleware(['auth', 'verified', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('dashboard', ['role' => 'Instructor']);
        })->name('dashboard');

    });

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
| Uses Route::controller() for cleaner syntax
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
| Additional Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
