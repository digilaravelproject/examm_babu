<?php

namespace App\Http\Controllers\Admin;

use App\Filters\QuestionFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\Topic;
use App\Models\Tag;
use App\Models\DifficultyLevel;
use App\Models\ComprehensionPassage;
use App\Repositories\QuestionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class QuestionController
 *
 * Manages Question Bank CRUD, settings updates, and Approval Workflow.
 * Enforces strict authorization and data integrity via transactions.
 */
class QuestionController_old extends Controller
{
    protected QuestionRepository $repository;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of questions with filters.
     *
     * @param QuestionFilters $filters
     * @return View|RedirectResponse
     */
    public function index(QuestionFilters $filters, Request $request): View|string
    {
        $query = Question::filter($filters)
            ->with(['questionType', 'skill', 'topic', 'difficultyLevel', 'section'])
            ->latest();

        // Instructor restrictions
        if (Auth::user()->hasRole('instructor')) {
            $query->where('created_by', Auth::id());
        }

        $questions = $query->paginate(10)->withQueryString();

        // --- FIX START ---
        // If the request comes from AlpineJS/JavaScript (AJAX), return ONLY the table partial
        if ($request->ajax()) {
            return view('admin.questions.partials.questions-table', compact('questions'))->render();
        }
        // --- FIX END ---

        $types = QuestionType::where('is_active', 1)->get();
        $skills = Skill::where('is_active', 1)->select('id', 'name')->get();

        // View determination
        $viewName = request()->routeIs('instructor.*') ? 'instructor.questions.index' : 'admin.questions.index';
        if (!view()->exists($viewName)) {
            $viewName = 'admin.questions.index';
        }

        return view($viewName, compact('questions', 'types', 'skills'));
    }
    public function index_old(QuestionFilters $filters): View|RedirectResponse
    {
        try {
            $query = Question::filter($filters)
                ->with(['questionType', 'skill', 'topic', 'difficultyLevel', 'section'])
                ->latest();

            // Strict Instructor Restriction
            if (Auth::user()->hasRole('instructor')) {
                $query->where('created_by', Auth::id());
            }

            $questions = $query->paginate(10)->withQueryString();
            $types = QuestionType::where('is_active', 1)->get();
            $skills = Skill::where('is_active', 1)->select('id', 'name')->get();

            // Determine view based on route prefix
            $viewName = request()->routeIs('instructor.*') ? 'instructor.questions.index' : 'admin.questions.index';

            if (!view()->exists($viewName)) {
                $viewName = 'admin.questions.index';
            }

            return view($viewName, compact('questions', 'types', 'skills'));

        } catch (\Exception $e) {
            Log::error('Question Index Error: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'Unable to load questions list.');
        }
    }

    /**
     * Show Pending Questions (Admin Only).
     *
     * @return View|RedirectResponse
     */
    public function pending(): View|RedirectResponse
    {
        try {
            // Strict Admin Check
            if (!Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized. Only admins can view pending questions.');
            }

            $questions = Question::with(['questionType', 'skill', 'topic', 'creator'])
                ->where('is_active', false)
                ->latest()
                ->paginate(10);

            return view('admin.questions.pending', compact('questions'));

        } catch (\Exception $e) {
            Log::error('Pending Questions Error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load pending questions.');
        }
    }

    /**
     * Approve a specific question (Admin Only).
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function approve($id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            if (!Auth::user()->hasRole('admin')) {
                throw new \Exception('Unauthorized action.');
            }

            $question = Question::findOrFail($id);
            $question->update(['is_active' => true]);

            DB::commit();
            return redirect()->back()->with('success', 'Question approved and is now live.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Approval Error: ' . $e->getMessage(), ['question_id' => $id]);
            return redirect()->back()->with('error', 'Unable to approve question.');
        }
    }

    /**
     * Load Preview Modal Content.
     *
     * @param int $id
     * @return View
     */
    public function preview($id): View
    {
        try {
            $question = Question::with(['questionType', 'skill', 'topic', 'difficultyLevel', 'section'])->findOrFail($id);

            // Use helper for consistency
            $this->authorizeInstructor($question);

            return view('admin.questions.partials.preview', compact('question'));

        } catch (\Exception $e) {
            Log::error('Preview Error: ' . $e->getMessage(), ['id' => $id]);
            abort(404, 'Question not found or access denied.');
        }
    }

    /**
     * Show the form for creating a new question.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function create(Request $request): View|RedirectResponse
    {
        try {
            $typeCode = $request->get('type', 'MSA');
            $questionType = QuestionType::where('code', $typeCode)->firstOrFail();
            $skills = Skill::where('is_active', 1)->get();

            $defaultOptions = $this->repository->setDefaultOptions($questionType->code);
            $defaultPreferences = $this->repository->setDefaultPreferences($questionType->code);

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.create' : 'admin.questions.create';
            return view($viewPath, compact('questionType', 'skills', 'defaultOptions', 'defaultPreferences'));

        } catch (\Exception $e) {
            Log::error('Create Page Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load creation form.');
        }
    }

    /**
     * Store a newly created question.
     *
     * @param StoreQuestionRequest $request
     * @return RedirectResponse
     */
    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();

            // Approval Logic: Admins auto-approve; Instructors go to pending
            if (Auth::user()->hasRole('admin')) {
                $data['is_active'] = true;
                $msg = 'Question created successfully.';
            } else {
                $data['is_active'] = false;
                $msg = 'Question submitted for approval.';
            }

            // Specific Logic for FIB or other complex types
            if ($request->question_type_id == 7) {
                // Logic for FIB blanks would go here
            }

            $question = Question::create($data);

            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.edit', ['question' => $question->id, 'tab' => 'settings'])
                             ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Store Error: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'Error creating question: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the edit form.
     *
     * @param Request $request
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit(Request $request, $id): View|RedirectResponse
    {
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $activeTab = $request->get('tab', 'details');

            // Load Dependencies
            $questionType = $question->questionType;
            $skills = Skill::where('is_active', 1)->get();
            $topics = Topic::where('skill_id', $question->skill_id)->get();
            $difficultyLevels = DifficultyLevel::all();
            $tags = Tag::all();
            $passages = ComprehensionPassage::all();

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.edit' : 'admin.questions.edit';
            return view($viewPath, compact(
                'question', 'activeTab', 'questionType', 'skills',
                'topics', 'difficultyLevels', 'tags', 'passages'
            ));

        } catch (\Exception $e) {
            Log::error('Edit Page Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Unable to load edit page.');
        }
    }

    /**
     * Update Basic Details (Tab 1).
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            // Re-approval logic: If Instructor edits, reset status to Pending (false)
            // If Admin edits, keep existing status
            $status = Auth::user()->hasRole('admin') ? $question->is_active : false;

            $question->update([
                'question' => $request->question,
                'options' => $request->options,
                'correct_answer' => $request->correct_answer,
                'is_active' => $status
            ]);

            DB::commit();

            $msg = ($status === false && !Auth::user()->hasRole('admin'))
                ? 'Updated. Sent for re-approval.'
                : 'Details updated successfully.';

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Update Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Update failed.');
        }
    }

    /**
     * Update Settings (Tab 2).
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateSettings(Request $request, $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $question->update($request->only(['skill_id', 'topic_id', 'difficulty_level_id', 'default_marks', 'default_time']));

            // Handle Tags Sync if needed
            if ($request->has('tags')) {
                 // $question->tags()->sync($request->tags);
            }

            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.edit', ['question' => $id, 'tab' => 'solution'])
                             ->with('success', 'Settings saved.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settings Update Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Failed to save settings.');
        }
    }

    /**
     * Update Solution (Tab 3).
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateSolution(Request $request, $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $question->update($request->only(['hint', 'solution', 'solution_video']));

            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.edit', ['question' => $id, 'tab' => 'attachment'])
                             ->with('success', 'Solution saved.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Solution Update Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Failed to save solution.');
        }
    }

    /**
     * Update Attachment (Tab 4).
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateAttachment(Request $request, $id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $question->update([
                'has_attachment' => $request->has_attachment,
                'attachment_type' => $request->attachment_type,
                'comprehension_passage_id' => $request->attachment_type == 'comprehension' ? $request->comprehension_id : null,
            ]);

            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.index')->with('success', 'Question setup complete!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attachment Update Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Failed to save attachment.');
        }
    }

    /**
     * Delete Question.
     *
     * @param int $id
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $question->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Question deleted.']);
            }

            return redirect()->back()->with('success', 'Question deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Delete Error: ' . $e->getMessage(), ['id' => $id]);

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting question.'], 500);
            }
            return redirect()->back()->with('error', 'Error deleting question.');
        }
    }

    /**
     * Helper to check authorization.
     * Aborts if an instructor tries to access a question they didn't create.
     *
     * @param Question $question
     * @return void
     */
    private function authorizeInstructor(Question $question): void
    {
        if (Auth::user()->hasRole('instructor') && $question->created_by != Auth::id()) {
            Log::warning('Unauthorized access attempt by Instructor', [
                'user_id' => Auth::id(),
                'question_id' => $question->id
            ]);
            abort(403, 'You are not authorized to modify this question.');
        }
    }
}
