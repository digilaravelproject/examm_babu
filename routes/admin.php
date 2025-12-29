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
use App\Http\Controllers\Admin\PlanCrudController;
use App\Http\Controllers\Admin\SubscriptionCrudController;
use App\Http\Controllers\Admin\PaymentController;
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
    Route::resource('subscriptions', SubscriptionCrudController::class);
    Route::get('subscriptions/invoice/{paymentId}', [SubscriptionCrudController::class, 'downloadInvoice'])
        ->name('subscriptions.invoice');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show'); // New
    Route::put('payments/{id}', [PaymentController::class, 'update'])->name('payments.update'); // New
    Route::post('payments/{id}/authorize', [PaymentController::class, 'authorizePayment'])->name('payments.authorize');
    Route::get('payments/{id}/invoice', [PaymentController::class, 'downloadInvoice'])->name('payments.invoice');

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




    // 1. Exam Details (Create/Edit/List)
    Route::resource('exams', ExamController::class);

    // Group for steps that require an existing Exam ID
    Route::prefix('exams/{exam}')->name('exams.')->group(function () {

        // 2. Settings
        Route::get('settings', [ExamController::class, 'settings'])->name('settings');
        Route::post('settings', [ExamController::class, 'updateSettings'])->name('settings.update');

        // 3. Sections (Resource Controller)
        Route::resource('sections', ExamSectionController::class)->except(['show']);

        // 4. Questions (Main Page)
        Route::get('questions', [ExamQuestionController::class, 'index'])->name('questions.index');
        Route::get('/all-question-ids', [ExamQuestionController::class, 'fetchAllExamQuestionIds']);

        // --- AJAX Routes for Question Logic (Add these) ---
        // Fetch questions added to a specific section
        Route::get('sections/{section}/questions', [ExamQuestionController::class, 'fetchExamQuestions'])
            ->name('questions.fetch');

        // Fetch available questions from Question Bank
        Route::get('sections/{section}/questions/available', [ExamQuestionController::class, 'fetchAvailableQuestions'])
            ->name('questions.available');

        // Add a question to a section
        Route::post('sections/{section}/questions/add', [ExamQuestionController::class, 'addQuestion'])
            ->name('questions.add');

        // Remove a question from a section
        Route::post('sections/{section}/questions/remove', [ExamQuestionController::class, 'removeQuestion'])
            ->name('questions.remove');


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

    /*
    |--------------------------------------------------------------------------
    | Monetization Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/search_plans', [PlanCrudController::class, 'search'])->name('search_plans');
    Route::resource('plans', PlanCrudController::class);

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
