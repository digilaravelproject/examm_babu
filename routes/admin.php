<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFileManagerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComprehensionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamQuestionController;
use App\Http\Controllers\Admin\ExamScheduleController;
use App\Http\Controllers\Admin\ExamSectionController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\PracticeSetsController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\QuestionTypeController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuizTypeController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (Prefix: /admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // --- DASHBOARD & SYSTEM ---
    Route::controller(AdminDashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/chart-data', 'getChartData')->name('dashboard.chart');
        Route::get('/system/optimize', 'optimize')->name('system.optimize');
    });

    // --- USER & ROLE MANAGEMENT ---
    Route::controller(RolePermissionController::class)->prefix('roles-permissions')->name('roles_permissions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/assign', 'assignPermission')->name('assign');
    });

    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // --- QUESTION MANAGEMENT (Order is Very Important Here) ---

    // =========================================================
    // 1. BULK IMPORT ROUTES (NO QUEUE / JS CHUNKING)
    // Important: Inhe 'resource' route se PEHLE rakhna zaroori hai
    // =========================================================

    // Import Page Dikhane ke liye
    Route::get('questions/import', [QuestionImportController::class, 'showImportForm'])
        ->name('questions.import');

    // Sample Excel Download karne ke liye
    Route::get('questions/import/sample', [QuestionImportController::class, 'downloadSample'])
        ->name('questions.import.sample');

    // Step 1: File Upload & JSON Preparation (Total Count return karega)
    Route::post('questions/import/prepare', [QuestionImportController::class, 'uploadAndPrepare'])
        ->name('questions.import.prepare');

    // Step 2: Chunk Processing (Loop mein call hoga)
    Route::post('questions/import/chunk', [QuestionImportController::class, 'processChunk'])
        ->name('questions.import.chunk');

    // =========================================================
    // 2. QUESTION RESOURCE ROUTE
    // =========================================================
    Route::resource('questions', QuestionController::class);

    // 2. Custom Question Actions
    Route::controller(QuestionController::class)->prefix('questions')->name('questions.')->group(function () {
        Route::get('/pending', 'pending')->name('pending');
        Route::patch('/{question}/approve', 'approve')->name('approve');
        Route::get('/{question}/preview', 'preview')->name('preview');

        // Step Updates (Tabs)
        Route::put('/{question}/settings', 'updateSettings')->name('update_settings');
        Route::put('/{question}/solution', 'updateSolution')->name('update_solution');
        Route::put('/{question}/attachment', 'updateAttachment')->name('update_attachment');
    });

    // 3. Standard Resource (Keep this at the end of question section)
    // Route::resource('questions', QuestionController::class);
    Route::resource('comprehensions', ComprehensionController::class);
    Route::get('question-types', [QuestionTypeController::class, 'index'])->name('question-types.index');
    Route::resource('categories', CategoryController::class);
    // Sub-Catogries
    Route::resource('sub-categories', SubCategoryController::class);
    // Sections mapping ke liye
    Route::get('fetch-sub-category-sections/{id}', [SubCategoryController::class, 'fetchSections'])->name('sub-categories.sections.fetch');
    Route::post('update-sub-category-sections/{id}', [SubCategoryController::class, 'updateSections'])->name('sub-categories.sections.update');
    // Tag Resource (Index, Create, Store, Edit, Update, Destroy)
    Route::resource('tags', TagController::class);
    // Search Route (AJAX/API endpoint for tag suggestions)
    Route::get('search-tags', [TagController::class, 'search'])->name('tags.search');
    Route::resource('sections', SectionController::class);
    Route::get('search-sections', [SectionController::class, 'search'])->name('sections.search');
    Route::resource('skills', SkillController::class);
    Route::get('search-skills', [SkillController::class, 'search'])->name('skills.search');
    Route::resource('topics', TopicController::class);
    Route::get('search-topics', [TopicController::class, 'search'])->name('topics.search');


// // Exam CRUD
//     Route::resource('exams', ExamController::class);
//     Route::get('exams/{exam}/settings', [ExamController::class, 'settings'])->name('exams.settings');
//     Route::post('exams/{exam}/settings', [ExamController::class, 'updateSettings'])->name('exams.settings.update');

//     // Exam Sections
//     Route::resource('exams.sections', ExamSectionController::class)->shallow();

//     // Exam Schedules
//     Route::resource('exams.schedules', ExamScheduleController::class)->shallow();

// 1. Exam Details (Create/Edit/List)
    Route::resource('exams', ExamController::class);

    // Group for steps that require an existing Exam ID
    Route::prefix('exams/{exam}')->name('exams.')->group(function () {

        // 2. Settings
        Route::get('settings', [ExamController::class, 'settings'])->name('settings');
        Route::post('settings', [ExamController::class, 'updateSettings'])->name('settings.update');

        // 3. Sections
        Route::resource('sections', ExamSectionController::class)->except(['show']);

        // 4. Questions (Logic to link questions to sections)
        Route::get('questions', [ExamQuestionController::class, 'index'])->name('questions.index');
        Route::post('questions/store', [ExamQuestionController::class, 'store'])->name('questions.store');
        Route::delete('questions/{question}', [ExamQuestionController::class, 'destroy'])->name('questions.destroy');

        // 5. Schedules
        Route::resource('schedules', ExamScheduleController::class)->except(['show']);
    });

    // --- ACADEMIC ROUTES ---
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

    // --- TOOLS & LOGS ---
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

    Route::get('/dashboard', function () {
        return view('instructor.dashboard');
    })->name('dashboard');

    // Custom Actions for Instructor
    Route::controller(QuestionController::class)->prefix('questions')->name('questions.')->group(function () {
        Route::get('/{question}/preview', 'preview')->name('preview');
        Route::put('/{question}/settings', 'updateSettings')->name('update_settings');
        Route::put('/{question}/solution', 'updateSolution')->name('update_solution');
        Route::put('/{question}/attachment', 'updateAttachment')->name('update_attachment');
    });

    Route::resource('questions', QuestionController::class);
});
