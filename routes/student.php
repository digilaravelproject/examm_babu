<?php

use App\Http\Controllers\Student\StudentDashboardController;
use Illuminate\Support\Facades\Route;

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
