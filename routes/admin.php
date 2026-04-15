<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFileManagerController;
use App\Http\Controllers\Admin\AiImportController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComprehensionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamQuestionController;
use App\Http\Controllers\Admin\ExamScheduleController;
use App\Http\Controllers\Admin\ExamSectionController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\MicroCategoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanCrudController;
use App\Http\Controllers\Admin\PracticeSetController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\QuestionTypeController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuizTypeController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\ReferralOverrideController;
use App\Http\Controllers\Admin\ReferralSettingController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\SubscriptionCrudController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\HomeStatController;
use App\Http\Controllers\Admin\HomeFeatureController;
use App\Http\Controllers\Admin\AdvertisementController;

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (Prefix: /admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // --- 1. DASHBOARD & SYSTEM ---
    Route::controller(AdminDashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/chart-data', 'getChartData')->name('dashboard.chart');
        Route::get('/system/optimize', 'optimize')->name('system.optimize');
    });

    // --- 2. SETTINGS (General, Email, Payment, Billing, Referral) ---
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::post('/general', [SettingController::class, 'updateSiteSettings'])->name('update-site');
        Route::post('/logo', [SettingController::class, 'updateLogo'])->name('update-logo');
        Route::post('/favicon', [SettingController::class, 'updateFavicon'])->name('update-favicon');

        Route::get('/email', [SettingController::class, 'email'])->name('email');
        Route::post('/email', [SettingController::class, 'updateEmailSettings'])->name('update-email');

        Route::get('/payment', [SettingController::class, 'payment'])->name('payment');
        Route::post('/payment-currency', [SettingController::class, 'updatePaymentSettings'])->name('update-payment');
        Route::post('/razorpay', [SettingController::class, 'updateRazorpaySettings'])->name('update-razorpay');

        Route::get('/billing', [SettingController::class, 'billing'])->name('billing');
        Route::post('/billing', [SettingController::class, 'updateBillingSettings'])->name('update-billing');

        Route::get('/ai', [SettingController::class, 'ai'])->name('ai');
        Route::post('/ai', [SettingController::class, 'updateAiSettings'])->name('update-ai');

        Route::get('/referral', [ReferralSettingController::class, 'index'])->name('referral');
        Route::post('/referral', [ReferralSettingController::class, 'update'])->name('referral.update');
    });

    // --- 3. REFERRALS MANAGEMENT ---
    Route::controller(ReferralController::class)->prefix('referrals')->as('referrals.')->group(function () {
        Route::get('history', 'history')->name('history');
        Route::get('withdrawals', 'withdrawals')->name('withdrawals');
        Route::post('withdrawals/{id}/approve', 'approveWithdrawal')->name('approve');
        Route::post('withdrawals/{id}/reject', 'rejectWithdrawal')->name('reject');
    });
    // List Users
    Route::get('referral/users', [ReferralOverrideController::class, 'index'])->name('referral.users');

    // Update Specific User
    Route::post('referral/users/{user}/update', [ReferralOverrideController::class, 'update'])->name('referral.users.update');
    // --- 4. USER & ROLE MANAGEMENT ---
    Route::controller(RolePermissionController::class)->group(function () {
        Route::get('roles-permissions', 'index')->name('roles_permissions.index');
        Route::post('roles/store', 'storeRole')->name('roles.store');
        Route::post('roles/update-permission', 'updateRolePermission')->name('roles.update_perm');
        Route::get('users/search', 'searchUser')->name('users.search');
        Route::get('users/{id}/permissions', 'getUserPermissions')->name('users.get_perms');
        Route::post('users/update-permission', 'updateUserPermission')->name('users.update_perm');
    });

    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::resource('user-groups', UserGroupController::class);

    // --- 5. QUESTION BANK MANAGEMENT ---

    // Import Routes
    Route::get('questions/import', [QuestionImportController::class, 'showImportForm'])->name('questions.import');
    Route::get('questions/import/sample', [QuestionImportController::class, 'downloadSample'])->name('questions.import.sample');
    Route::post('questions/import/prepare', [QuestionImportController::class, 'uploadAndPrepare'])->name('questions.import.prepare');
    Route::post('questions/import/chunk', [QuestionImportController::class, 'processChunk'])->name('questions.import.chunk');

    // // AI Import Page
    Route::get('ai-import', [AiImportController::class, 'index'])->name('ai-import.index');
    Route::get('ai-import/preview/{batch_id}', [AiImportController::class, 'preview'])->name('ai-import.preview');
    Route::post('ai-import/process', [AiImportController::class, 'uploadAndProcess'])->name('ai-import.process');
    Route::post('ai-import/upload-cropped-image', [AiImportController::class, 'uploadCroppedImage'])->name('ai-import.upload-cropped-image');
    Route::post('ai-import/cancel', [AiImportController::class, 'cancelImport'])->name('ai-import.cancel');
    Route::post('ai-import/approve/{batch_id}', [AiImportController::class, 'approve'])->name('ai-import.approve');

    // General Question Routes
    Route::get('questions/{id}/usage', [QuestionController::class, 'usage'])->name('questions.usage');
    Route::post('questions/bulk-delete', [QuestionController::class, 'bulkDestroy'])->name('questions.bulk_destroy');
    Route::patch('questions/{id}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('questions.toggle_status');

    // Question Review & Steps
    Route::controller(QuestionController::class)->prefix('questions')->name('questions.')->group(function () {
        Route::get('/pending', 'pending')->name('pending');
        Route::patch('/{question}/approve', 'approve')->name('approve');
        Route::get('/{question}/preview', 'preview')->name('preview');
        Route::put('/{question}/settings', 'updateSettings')->name('update_settings');
        Route::put('/{question}/solution', 'updateSolution')->name('update_solution');
        Route::put('/{question}/attachment', 'updateAttachment')->name('update_attachment');
    });

    Route::resource('questions', QuestionController::class);

    // Comprehension Routes
    Route::get('comprehensions/{comprehension}/usage', [ComprehensionController::class, 'usage'])->name('comprehensions.usage');
    Route::post('comprehensions/store-ajax', [ComprehensionController::class, 'storeAjax'])->name('comprehensions.store_ajax');
    Route::resource('comprehensions', ComprehensionController::class);
    Route::get('question-types', [QuestionTypeController::class, 'index'])->name('question-types.index');

    // --- 6. CATEGORIES, TOPICS, TAGS, SKILLS ---
    Route::resource('categories', CategoryController::class);
    Route::resource('sub-categories', SubCategoryController::class);
    Route::resource('micro-categories', MicroCategoryController::class);

    Route::get('sub-categories/{id}/sections', [SubCategoryController::class, 'fetchSections'])->name('sub-categories.sections.fetch');
    Route::post('sub-categories/{id}/sections', [SubCategoryController::class, 'updateSections'])->name('sub-categories.sections.update');

    Route::resource('tags', TagController::class);
    Route::get('search-tags', [TagController::class, 'search'])->name('tags.search');

    Route::resource('sections', SectionController::class);
    Route::get('search-sections', [SectionController::class, 'search'])->name('sections.search');

    Route::resource('skills', SkillController::class);
    Route::get('search-skills', [SkillController::class, 'search'])->name('skills.search');

    Route::resource('topics', TopicController::class);
    Route::get('search-topics', [TopicController::class, 'search'])->name('topics.search');

    // --- 7. EXAMS & QUIZZES MANAGEMENT ---

    // Exam CRUD & Duplication
    Route::post('exams/{exam}/duplicate-exam', [ExamController::class, 'duplicate'])->name('exams.duplicate');
    Route::resource('exams', ExamController::class);
    Route::resource('exam-types', ExamTypeController::class);
    Route::post('/exams/{exam}/status-action', [ExamController::class, 'statusAction'])->name('exams.status_action');
    Route::post('exams/{exam}/quick-publish', [ExamController::class, 'quickPublish'])->name('exams.quick-publish');

    // Exam Steps & Builder
    Route::prefix('exams/{exam}')->name('exams.')->group(function () {
        Route::get('preview', [ExamController::class, 'preview'])->name('preview');

        Route::get('settings', [ExamController::class, 'settings'])->name('settings');
        Route::post('settings', [ExamController::class, 'updateSettings'])->name('settings.update');

        Route::resource('sections', ExamSectionController::class)->except(['show']);

        // Questions in Exam
        Route::get('questions', [ExamQuestionController::class, 'index'])->name('questions.index');
        Route::get('/all-question-ids', [ExamQuestionController::class, 'fetchAllExamQuestionIds']);
        Route::get('sections/{section}/questions', [ExamQuestionController::class, 'fetchExamQuestions'])->name('questions.fetch');
        Route::get('sections/{section}/questions/available', [ExamQuestionController::class, 'fetchAvailableQuestions'])->name('questions.available');
        Route::post('sections/{section}/questions/add', [ExamQuestionController::class, 'addQuestion'])->name('questions.add');
        Route::post('sections/{section}/questions/remove', [ExamQuestionController::class, 'removeQuestion'])->name('questions.remove');

        // Schedules
        Route::resource('schedules', ExamScheduleController::class)->except(['show']);
        Route::get('schedules/{schedule}/analytics', [ExamScheduleController::class, 'analytics'])->name('schedules.analytics');
    });

    // Quizzes & Practice Sets
    Route::controller(QuizController::class)->prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
    });
    Route::get('/quiz-types/index', [QuizTypeController::class, 'index'])->name('quiz-types.index');

    Route::controller(ExamController::class)->prefix('exam')->name('exam.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
    });

    // Practice Sets
    Route::resource('practice-sets', PracticeSetController::class);
    Route::get('practice-sets/{practice_set}/settings', [PracticeSetController::class, 'settings'])->name('practice-sets.settings');
    Route::post('practice-sets/{practice_set}/settings', [PracticeSetController::class, 'updateSettings'])->name('practice-sets.settings.update');
    Route::get('practice-sets/{practice_set}/report', [PracticeSetController::class, 'overallReport'])->name('practice-sets.overall_report');


    // --- 8. PAYMENTS & SUBSCRIPTIONS ---
    Route::resource('subscriptions', SubscriptionCrudController::class);
    Route::get('subscriptions/invoice/{paymentId}', [SubscriptionCrudController::class, 'downloadInvoice'])->name('subscriptions.invoice');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
    Route::put('payments/{id}', [PaymentController::class, 'update'])->name('payments.update');
    Route::post('payments/{id}/authorize', [PaymentController::class, 'authorizePayment'])->name('payments.authorize');
    Route::get('payments/{id}/invoice', [PaymentController::class, 'downloadInvoice'])->name('payments.invoice');

    Route::get('/search_plans', [PlanCrudController::class, 'search'])->name('search_plans');
    Route::resource('plans', PlanCrudController::class);

    // --- 9. TOOLS & LOGS ---
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('logs');

    Route::controller(AdminFileManagerController::class)->prefix('file-manager')->name('fm.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/ckeditor', 'ckeditor')->name('ckeditor');
        Route::get('/popup', 'popup')->name('popup');
    });


    // Hero Slider CRUD
    Route::resource('hero-slides', HeroSlideController::class);
    Route::post('hero-slides/{id}/toggle', [HeroSlideController::class, 'toggleStatus'])->name('hero-slides.toggle');


    Route::resource('home-stats', HomeStatController::class);
    Route::post('home-stats/{id}/toggle', [HomeStatController::class, 'toggleStatus'])->name('home-stats.toggle');

    Route::resource('home-features', HomeFeatureController::class);
    Route::post('home-features/{id}/toggle', [HomeFeatureController::class, 'toggleStatus'])->name('home-features.toggle');

    // Advertisements
    Route::resource('advertisements', AdvertisementController::class);
    Route::post('advertisements/{id}/toggle', [AdvertisementController::class, 'toggle'])->name('advertisements.toggle');
});
