<?php

use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\SyllabusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:student', 'check.syllabus'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // --- Syllabus Management (Exempted from check to allow changing syllabus) ---
        Route::controller(SyllabusController::class)
            ->withoutMiddleware(['check.syllabus'])
            ->group(function () {
                Route::get('/change-syllabus', 'changeSyllabus')->name('change_syllabus');
                Route::post('/update-syllabus', 'updateSyllabus')->name('update_syllabus');
                Route::get('/get-current-syllabus', 'getCurrentSyllabus')->name('get_current_syllabus');
            });

        // --- Dashboard & Exams ---
        Route::controller(StudentDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/add-exams', 'addExams')->name('add_exams');
        });

        // --- Demo Interface ---
        Route::get('/exam-demo', function () {
            return view('student.exam-interface');
        })->name('exam_demo');

    });
