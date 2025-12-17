<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
// Naye Controllers Import kiye hain
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\QuizTypeController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\PracticeSetsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|
|
*/
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| Default / Fallback Dashboard
|--------------------------------------------------------------------------
| Agar user ke paas koi specific dashboard nahi hai, to wo yahan aayega.
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN & MANAGEMENT ROUTES (Permission Based)
|--------------------------------------------------------------------------
| Yahan hum 'role:admin' use NAHI kar rahe. Hum 'can:permission_name' use kar rahe hain.
| Agar tum kal ko kisi 'Manager' ko bhi 'manage roles' ki permission doge,
| to wo bhi ye page access kar payega without being Admin.
*/
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // 1. Admin Dashboard (Check: view dashboard permission)
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('can:view dashboard')
        ->name('dashboard');

    // 2. Roles & Permissions Matrix (Check: manage roles permission)
    Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
        ->middleware('can:manage roles')
        ->name('roles_permissions.index');

    Route::post('/roles-permissions/assign', [RolePermissionController::class, 'assignPermission'])
        ->middleware('can:manage roles')
        ->name('roles_permissions.assign');

    // 3. Activity Logs (Check: view logs permission)
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('can:view logs')
        ->name('logs');

    Route::get('/quizzes/index', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');

    Route::get('/exam/index', [ExamController::class, 'index'])->name('exam.index');
    Route::get('/exam/create', [ExamController::class, 'create'])->name('exam.create');

    Route::get('/quiz-types/index', [QuizTypeController::class, 'index'])->name('quiz-types.index');

    Route::get('/exam-types/index', [ExamTypeController::class, 'index'])->name('exam-types.index');

    Route::get('/practice-sets/index', [PracticeSetsController::class, 'index'])->name('practice-sets.index');
    Route::get('/practice-sets/create', [PracticeSetsController::class, 'create'])->name('practice-sets.create');
});


/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/exam-demo', function () {
    return view('student.exam-interface');
})->name('exam_demo');
});

/*
|--------------------------------------------------------------------------
| INSTRUCTOR ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['role' => 'Instructor']);
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Profile Routes (Default Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
