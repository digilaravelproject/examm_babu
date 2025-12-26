<?php

use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\SyllabusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
*/

// YAHAN dekhiye, maine 'check.syllabus' add kar diya hai array me
Route::middleware(['auth', 'verified', 'role:student', 'check.syllabus'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // --- Exception Routes (Middleware inhe ignore karega logic ke hisaab se) ---
        Route::get('/change-syllabus', [SyllabusController::class, 'changeSyllabus'])->name('change_syllabus');
        Route::post('/update-syllabus', [SyllabusController::class, 'updateSyllabus'])->name('update_syllabus');
        // AJAX Route for fetching syllabus name
        Route::get('/get-current-syllabus', [SyllabusController::class, 'getCurrentSyllabus'])
            ->name('get_current_syllabus');

        // --- Protected Routes (Agar syllabus nahi hai, to yahan nahi aa payenge) ---
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/add-exams', [StudentDashboardController::class, 'addExams'])->name('add_exams');

        Route::get('/exam-demo', function () {
            return view('student.exam-interface');
        })->name('exam_demo');

    });
