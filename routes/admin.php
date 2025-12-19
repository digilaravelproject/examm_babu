<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFileManagerController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\PracticeSetsController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuizTypeController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\QuestionController; // NEW CONTROLLER

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (Prefix: /admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // --- EXISTING DASHBOARD & SYSTEM ROUTES ---
    Route::controller(AdminDashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/chart-data', 'getChartData')->name('dashboard.chart');
        Route::get('/system/optimize', 'optimize')->name('system.optimize');
    });

    // --- EXISTING USER & ROLE MANAGEMENT ---
    Route::controller(RolePermissionController::class)->prefix('roles-permissions')->name('roles_permissions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/assign', 'assignPermission')->name('assign');
    });

    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // --- QUESTION MANAGEMENT (ADMIN) ---
    Route::controller(QuestionController::class)->prefix('questions')->name('questions.')->group(function () {
        Route::get('/pending', 'pending')->name('pending'); // Admin Only: Approval List
        Route::patch('/{question}/approve', 'approve')->name('approve'); // Admin Only: Approve Action
        Route::get('/{question}/preview', 'preview')->name('preview');

        // Step Updates (Tabs)
        Route::put('/{question}/settings', 'updateSettings')->name('update_settings');
        Route::put('/{question}/solution', 'updateSolution')->name('update_solution');
        Route::put('/{question}/attachment', 'updateAttachment')->name('update_attachment');
    });
    Route::resource('questions', QuestionController::class); // Standard CRUD

    // --- EXISTING ACADEMIC ROUTES ---
    Route::controller(QuizController::class)->prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
    });

    Route::controller(ExamController::class)->prefix('exam')->name('exam.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
    });

    Route::controller(PracticeSetsController::class)->prefix('practice-sets')->name('practice-sets.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
    });

    Route::get('/quiz-types/index', [QuizTypeController::class, 'index'])->name('quiz-types.index');
    Route::get('/exam-types/index', [ExamTypeController::class, 'index'])->name('exam-types.index');

    // --- EXISTING TOOLS ---
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('logs');

    Route::controller(AdminFileManagerController::class)->prefix('file-manager')->name('fm.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/ckeditor', 'ckeditor')->name('ckeditor');
        Route::get('/popup', 'popup')->name('popup');
    });
});

/*
|--------------------------------------------------------------------------
| INSTRUCTOR ROUTES (Prefix: /instructor)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {

    // Instructor Dashboard (Placeholder)
    Route::get('/dashboard', function() { return view('instructor.dashboard'); })->name('dashboard');

    // --- QUESTION MANAGEMENT (INSTRUCTOR) ---
    // Hum same controller use karenge, par logic andar handle hoga
    Route::controller(QuestionController::class)->prefix('questions')->name('questions.')->group(function () {

        // Step Updates
        Route::put('/{question}/settings', 'updateSettings')->name('update_settings');
        Route::put('/{question}/solution', 'updateSolution')->name('update_solution');
        Route::put('/{question}/attachment', 'updateAttachment')->name('update_attachment');
    });
    Route::resource('questions', QuestionController::class);
});
