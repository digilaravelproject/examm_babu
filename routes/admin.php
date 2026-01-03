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
use App\Http\Controllers\Admin\PracticeSetController;
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
use App\Http\Controllers\Admin\MicroCategoryController;
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

    // =========================================================
    // QUESTION MANAGEMENT
    // =========================================================

    // 1. Bulk Import Routes
    Route::get('questions/import', [QuestionImportController::class, 'showImportForm'])->name('questions.import');
    Route::get('questions/import/sample', [QuestionImportController::class, 'downloadSample'])->name('questions.import.sample');
    Route::post('questions/import/prepare', [QuestionImportController::class, 'uploadAndPrepare'])->name('questions.import.prepare');
    Route::post('questions/import/chunk', [QuestionImportController::class, 'processChunk'])->name('questions.import.chunk');

    // 2. Custom Question Actions (Must be before resource)
    Route::post('questions/bulk-delete', [QuestionController::class, 'bulkDestroy'])->name('questions.bulk_destroy');
    Route::get('questions/{question}/preview', [QuestionController::class, 'preview'])->name('questions.preview');
    Route::get('questions/pending', [QuestionController::class, 'pending'])->name('questions.pending');
    Route::patch('questions/{question}/approve', [QuestionController::class, 'approve'])->name('questions.approve');
    Route::get('questions/{id}/usage', [QuestionController::class, 'usage'])->name('questions.usage');

    // 3. Question Resource
    Route::resource('questions', QuestionController::class);

    // =========================================================
    // PRACTICE SETS
    // =========================================================
    Route::get('practice-sets/{practice_set}/report', [PracticeSetController::class, 'overallReport'])->name('practice-sets.overall_report');
    Route::get('practice-sets/{practice_set}/settings', [PracticeSetController::class, 'settings'])->name('practice-sets.settings');
    Route::post('practice-sets/{practice_set}/settings', [PracticeSetController::class, 'updateSettings'])->name('practice-sets.settings.update');
    Route::resource('practice-sets', PracticeSetController::class);

    // =========================================================
    // EXAMS
    // =========================================================
    Route::post('exams/{exam}/duplicate-exam', [ExamController::class, 'duplicate'])->name('exams.duplicate');
    Route::resource('exams', ExamController::class);

    // Exam Steps Group
    Route::prefix('exams/{exam}')->name('exams.')->group(function () {
        Route::get('settings', [ExamController::class, 'settings'])->name('settings');
        Route::post('settings', [ExamController::class, 'updateSettings'])->name('settings.update');
        Route::resource('sections', ExamSectionController::class)->except(['show']);

        // Exam Questions
        Route::get('questions', [ExamQuestionController::class, 'index'])->name('questions.index');
        Route::get('/all-question-ids', [ExamQuestionController::class, 'fetchAllExamQuestionIds']);
        Route::get('sections/{section}/questions', [ExamQuestionController::class, 'fetchExamQuestions'])->name('questions.fetch');
        Route::get('sections/{section}/questions/available', [ExamQuestionController::class, 'fetchAvailableQuestions'])->name('questions.available');
        Route::post('sections/{section}/questions/add', [ExamQuestionController::class, 'addQuestion'])->name('questions.add');
        Route::post('sections/{section}/questions/remove', [ExamQuestionController::class, 'removeQuestion'])->name('questions.remove');

        Route::resource('schedules', ExamScheduleController::class)->except(['show']);
    });

    // =========================================================
    // ACADEMIC & MASTER DATA
    // =========================================================
    Route::resource('comprehensions', ComprehensionController::class);
    Route::get('question-types', [QuestionTypeController::class, 'index'])->name('question-types.index');
    Route::resource('categories', CategoryController::class);
    Route::resource('sub-categories', SubCategoryController::class);
    Route::resource('micro-categories', MicroCategoryController::class);
    Route::get('fetch-sub-category-sections/{id}', [SubCategoryController::class, 'fetchSections'])->name('sub-categories.sections.fetch');
    Route::post('update-sub-category-sections/{id}', [SubCategoryController::class, 'updateSections'])->name('sub-categories.sections.update');

    Route::resource('tags', TagController::class);
    Route::get('search-tags', [TagController::class, 'search'])->name('tags.search');

    Route::resource('sections', SectionController::class);
    Route::get('search-sections', [SectionController::class, 'search'])->name('sections.search');

    Route::resource('skills', SkillController::class);
    Route::get('search-skills', [SkillController::class, 'search'])->name('skills.search');

    Route::resource('topics', TopicController::class);
    Route::get('search-topics', [TopicController::class, 'search'])->name('topics.search');

    Route::controller(QuizController::class)->prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
    });

    Route::get('/quiz-types/index', [QuizTypeController::class, 'index'])->name('quiz-types.index');
    Route::get('/exam-types/index', [ExamTypeController::class, 'index'])->name('exam-types.index');

    // =========================================================
    // PAYMENTS & SUBSCRIPTIONS
    // =========================================================
    Route::resource('subscriptions', SubscriptionCrudController::class);
    Route::get('subscriptions/invoice/{paymentId}', [SubscriptionCrudController::class, 'downloadInvoice'])->name('subscriptions.invoice');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
    Route::put('payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
    Route::post('payments/{id}/authorize', [PaymentController::class, 'authorizePayment'])->name('payments.authorize');
    Route::get('payments/{id}/invoice', [PaymentController::class, 'downloadInvoice'])->name('payments.invoice');

    Route::get('/search_plans', [PlanCrudController::class, 'search'])->name('search_plans');
    Route::resource('plans', PlanCrudController::class);

    // =========================================================
    // TOOLS & LOGS
    // =========================================================
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
    Route::post('questions/bulk-delete', [QuestionController::class, 'bulkDestroy'])->name('questions.bulk_destroy');
    Route::get('questions/{question}/preview', [QuestionController::class, 'preview'])->name('questions.preview');
    Route::get('questions/{id}/usage', [QuestionController::class, 'usage'])->name('questions.usage');

    Route::resource('questions', QuestionController::class);
});
