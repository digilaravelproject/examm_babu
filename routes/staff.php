<?php

use Illuminate\Support\Facades\Route;

// --- Instructor Controllers ---
use App\Http\Controllers\Instructor\ReferralController;
use App\Http\Controllers\Instructor\WithdrawalController;

// --- Admin Controllers (Shared) ---
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\ComprehensionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamQuestionController;
use App\Http\Controllers\Admin\ExamSectionController;
use App\Http\Controllers\Admin\ExamScheduleController;
use App\Http\Controllers\Admin\ExamTypeController;

// --- Added Controllers for Master Data ---
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\MicroCategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TopicController;

Route::middleware(['auth', 'verified', 'check.dynamic.role'])
    ->prefix('{role}')
    ->name('panel.')
    ->group(function () {

        // --- 1. DASHBOARD ---
        Route::get('/dashboard', function () {
            return view('instructor.dashboard');
        })->name('dashboard');

        // --- 2. MASTER DATA MANAGEMENT (Categories, Tags, Skills, etc.) ---

        // Categories & Sub/Micro Categories
        Route::middleware(['can:manage categories'])->group(function () {
            Route::resource('categories', CategoryController::class);
            Route::resource('sub-categories', SubCategoryController::class);
            Route::resource('micro-categories', MicroCategoryController::class);

            // Sub-category specific routes (AJAX)
            Route::get('sub-categories/{id}/sections', [SubCategoryController::class, 'fetchSections'])->name('sub-categories.sections.fetch');
            Route::post('sub-categories/{id}/sections', [SubCategoryController::class, 'updateSections'])->name('sub-categories.sections.update');
        });

        // Tags
        Route::middleware(['can:manage tags'])->group(function () {
            Route::resource('tags', TagController::class);
            Route::get('search-tags', [TagController::class, 'search'])->name('tags.search');
        });

        // Sections
        Route::middleware(['can:manage sections'])->group(function () {
            Route::resource('sections', SectionController::class);
            Route::get('search-sections', [SectionController::class, 'search'])->name('sections.search');
        });

        // Skills
        Route::middleware(['can:manage skills'])->group(function () {
            Route::resource('skills', SkillController::class);
            Route::get('search-skills', [SkillController::class, 'search'])->name('skills.search');
        });

        // Topics
        Route::middleware(['can:manage topics'])->group(function () {
            Route::resource('topics', TopicController::class);
            Route::get('search-topics', [TopicController::class, 'search'])->name('topics.search');
        });


        // --- 3. QUESTION BANK MANAGEMENT (Global Questions) ---
        Route::prefix('questions')->name('questions.')->group(function () {
            // Import
            Route::middleware(['can:import questions'])->group(function () {
                Route::get('/import', [QuestionImportController::class, 'showImportForm'])->name('import');
                Route::get('/import/sample', [QuestionImportController::class, 'downloadSample'])->name('import.sample');
                Route::post('/import/prepare', [QuestionImportController::class, 'uploadAndPrepare'])->name('import.prepare');
                Route::post('/import/chunk', [QuestionImportController::class, 'processChunk'])->name('import.chunk');
            });

            // Create
            Route::middleware(['can:create questions'])->group(function () {
                Route::get('/create', [QuestionController::class, 'create'])->name('create');
                Route::post('/', [QuestionController::class, 'store'])->name('store');
            });

            // Bulk Delete
            Route::middleware(['can:delete questions'])->post('/bulk-delete', [QuestionController::class, 'bulkDestroy'])->name('bulk_destroy');

            // Edit & Update
            Route::middleware(['can:edit questions'])->group(function () {
                Route::get('/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
                Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
                Route::patch('/{id}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('toggle_status');
                Route::put('/{question}/settings', [QuestionController::class, 'updateSettings'])->name('update_settings');
                Route::put('/{question}/solution', [QuestionController::class, 'updateSolution'])->name('update_solution');
                Route::put('/{question}/attachment', [QuestionController::class, 'updateAttachment'])->name('update_attachment');
            });

            // View & Manage
            Route::middleware(['can:manage questions'])->group(function () {
                Route::get('/', [QuestionController::class, 'index'])->name('index');
                Route::get('/{id}/usage', [QuestionController::class, 'usage'])->name('usage');
                Route::get('/{question}/preview', [QuestionController::class, 'preview'])->name('preview');
                Route::get('/{question}', [QuestionController::class, 'show'])->name('show');
            });

            // Delete Single
            Route::middleware(['can:delete questions'])->delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
        });

        // --- 4. COMPREHENSION MANAGEMENT ---
        Route::prefix('comprehensions')->name('comprehensions.')->group(function () {
            Route::middleware(['can:create questions'])->group(function () {
                Route::get('/create', [ComprehensionController::class, 'create'])->name('create');
                Route::post('/', [ComprehensionController::class, 'store'])->name('store');
            });
            Route::middleware(['can:edit questions'])->group(function () {
                Route::get('/{comprehension}/edit', [ComprehensionController::class, 'edit'])->name('edit');
                Route::put('/{comprehension}', [ComprehensionController::class, 'update'])->name('update');
            });
            Route::middleware(['can:manage questions'])->group(function () {
                Route::get('/', [ComprehensionController::class, 'index'])->name('index');
                Route::get('/{comprehension}', [ComprehensionController::class, 'show'])->name('show');
            });
            Route::middleware(['can:delete questions'])->delete('/{comprehension}', [ComprehensionController::class, 'destroy'])->name('destroy');
        });

        // --- 5. EXAM MANAGEMENT ---

        // A. Exam Types
        Route::middleware(['can:manage exams'])->group(function () {
            Route::resource('exam-types', ExamTypeController::class)->except(['show']);
        });

        // B. Exam Routes
        Route::prefix('exams')->name('exams.')->group(function () {

            // Create & Store
            Route::middleware(['can:create exams'])->group(function () {
                Route::get('/create', [ExamController::class, 'create'])->name('create');
                Route::post('/', [ExamController::class, 'store'])->name('store');
                Route::post('/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('duplicate');
            });

            // Edit & Settings
            Route::middleware(['can:edit exams'])->group(function () {
                Route::get('/{exam}/edit', [ExamController::class, 'edit'])->name('edit');
                Route::put('/{exam}', [ExamController::class, 'update'])->name('update');

                Route::get('/{exam}/settings', [ExamController::class, 'settings'])->name('settings');
                Route::post('/{exam}/settings', [ExamController::class, 'updateSettings'])->name('settings.update');

                // Approval Workflow
                Route::post('/{exam}/submit-review', [ExamController::class, 'submitForReview'])->name('submit_review');
            });

            // Sections & Questions
            Route::middleware(['can:edit exams'])->group(function () {

                // Sections Resource
                Route::get('/{exam}/sections', [ExamSectionController::class, 'index'])->name('sections.index');
                Route::post('/{exam}/sections', [ExamSectionController::class, 'store'])->name('sections.store');
                Route::get('/{exam}/sections/{section}/edit', [ExamSectionController::class, 'edit'])->name('sections.edit');
                Route::put('/{exam}/sections/{section}', [ExamSectionController::class, 'update'])->name('sections.update');
                Route::delete('/{exam}/sections/{section}', [ExamSectionController::class, 'destroy'])->name('sections.destroy');

                // Questions inside Exam
                Route::get('/{exam}/questions', [ExamQuestionController::class, 'index'])->name('questions.index');
                Route::get('/{exam}/all-question-ids', [ExamQuestionController::class, 'fetchAllExamQuestionIds'])->name('questions.all_ids');

                // Fetching & Managing Questions in Sections
                Route::get('/{exam}/sections/{section}/questions', [ExamQuestionController::class, 'fetchExamQuestions'])->name('questions.fetch');
                Route::get('/{exam}/sections/{section}/questions/available', [ExamQuestionController::class, 'fetchAvailableQuestions'])->name('questions.available');

                Route::post('/{exam}/sections/{section}/questions/add', [ExamQuestionController::class, 'addQuestion'])->name('questions.add');
                Route::post('/{exam}/sections/{section}/questions/remove', [ExamQuestionController::class, 'removeQuestion'])->name('questions.remove');
            });

            // Schedules
             Route::middleware(['can:edit exams'])->group(function () {
                Route::get('/{exam}/schedules', [ExamScheduleController::class, 'index'])->name('schedules.index');
                Route::post('/{exam}/schedules', [ExamScheduleController::class, 'store'])->name('schedules.store');
                Route::get('/{exam}/schedules/{schedule}/edit', [ExamScheduleController::class, 'edit'])->name('schedules.edit');
                Route::put('/{exam}/schedules/{schedule}', [ExamScheduleController::class, 'update'])->name('schedules.update');
                Route::delete('/{exam}/schedules/{schedule}', [ExamScheduleController::class, 'destroy'])->name('schedules.destroy');

                // Analytics
                Route::get('/{exam}/schedules/{schedule}/analytics', [ExamScheduleController::class, 'analytics'])->name('schedules.analytics');
            });

            // View & Manage
            Route::middleware(['can:manage exams'])->group(function () {
                Route::get('/', [ExamController::class, 'index'])->name('index');
                Route::get('/{exam}/preview', [ExamController::class, 'preview'])->name('preview');
                Route::get('/{exam}/analytics', [ExamController::class, 'analytics'])->name('analytics');
            });

            // Delete
            Route::middleware(['can:delete exams'])->delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');
        });

        // --- 6. REFERRAL & EARNINGS ---
        Route::middleware(['can:access referral'])->group(function () {
            Route::get('/refer-and-earn', [ReferralController::class, 'index'])->name('referral.dashboard');
            Route::post('/payout/request', [WithdrawalController::class, 'store'])->name('referral.withdraw.store');
        });
    });
