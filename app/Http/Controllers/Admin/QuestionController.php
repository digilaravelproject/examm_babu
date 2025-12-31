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
use App\Services\QuestionService; // IMPORT SERVICE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    protected QuestionRepository $repository;
    protected QuestionService $questionService;

    public function __construct(QuestionRepository $repository, QuestionService $questionService)
    {
        $this->repository = $repository;
        $this->questionService = $questionService;
    }

    public function index(QuestionFilters $filters, Request $request): View|string
    {
        $query = Question::filter($filters)
            ->with(['questionType', 'skill', 'topic', 'difficultyLevel', 'section'])
            ->latest();

        if (Auth::user()->hasRole('instructor')) {
            $query->where('created_by', Auth::id());
        }

        $questions = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.questions.partials.questions-table', compact('questions'))->render();
        }

        $types = QuestionType::where('is_active', 1)->get();
        $skills = Skill::where('is_active', 1)->select('id', 'name')->get();

        $viewName = request()->routeIs('instructor.*') ? 'instructor.questions.index' : 'admin.questions.index';
        return view($viewName, compact('questions', 'types', 'skills'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        try {
            $typeCode = $request->get('type', 'MSA');
            $questionType = QuestionType::where('code', $typeCode)->firstOrFail();

            $skills = Skill::where('is_active', 1)->get();
            $topics = Topic::all();
            $difficultyLevels = DifficultyLevel::all();
            $tags = Tag::all();
            $passages = ComprehensionPassage::all();

            $defaultOptions = $this->repository->setDefaultOptions($questionType->code);
            $defaultPreferences = $this->repository->setDefaultPreferences($questionType->code);

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.create' : 'admin.questions.create';

            return view($viewPath, compact(
                'questionType',
                'skills',
                'topics',
                'difficultyLevels',
                'tags',
                'passages',
                'defaultOptions',
                'defaultPreferences'
            ));
        } catch (\Exception $e) {
            Log::error('Create Page Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load creation form.');
        }
    }

    // --- FIXED STORE METHOD ---
    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        try {
            // Uses Service to handle images, options, and logic
            $this->questionService->createQuestion($request->validated(), $request);

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.index')->with('success', 'Question created successfully.');
        } catch (\Exception $e) {
            Log::error('Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating question: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Request $request, $id): View|RedirectResponse
    {
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $activeTab = $request->get('tab', 'details');
            $questionType = $question->questionType;
            $skills = Skill::where('is_active', 1)->get();
            // $topics = Topic::where('skill_id', $question->skill_id)->get();
            // Saare topics bhejo taaki JS filter kar sake
            $topics = Topic::select('id', 'name', 'skill_id')->get();
            $difficultyLevels = DifficultyLevel::all();
            $tags = Tag::all();
            $passages = ComprehensionPassage::all();

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.edit' : 'admin.questions.edit';

            return view($viewPath, compact(
                'question',
                'activeTab',
                'questionType',
                'skills',
                'topics',
                'difficultyLevels',
                'tags',
                'passages'
            ));
        } catch (\Exception $e) {
            Log::error('Edit Page Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Unable to load edit page.');
        }
    }

    // --- FIXED UPDATE METHOD ---
    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            // Uses Service to handle updates (Including Skill, Topic, Images)
            // request->all() ensures all fields (skill_id, topic_id etc) are passed to service
            $this->questionService->updateQuestion($question, $request->all(), $request);

            return redirect()->back()->with('success', 'Question updated successfully.');
        } catch (\Exception $e) {
            Log::error('Update Error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
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

    private function authorizeInstructor(Question $question): void
    {
        if (Auth::user()->hasRole('instructor') && $question->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this question.');
        }
    }


    public function usage($id)
    {
        // Change: 'exams' ki jagah 'linkedExams' load kiya
        $question = Question::with(['linkedExams', 'skill', 'topic', 'questionType'])->findOrFail($id);

        $this->authorizeInstructor($question);

        $viewName = request()->routeIs('instructor.*') ? 'instructor.questions.usage' : 'admin.questions.usage';

        return view($viewName, compact('question'));
    }
}
