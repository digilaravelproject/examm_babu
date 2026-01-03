<?php

namespace App\Http\Controllers\Admin;

use App\Filters\QuestionFilters;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\Topic;
use App\Models\DifficultyLevel;
use App\Models\ComprehensionPassage;
use App\Repositories\QuestionRepository; // Repository Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    private QuestionRepository $repository;

    public function __construct(QuestionRepository $repository)
    {
        // Repository Injection
        $this->repository = $repository;
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

            $skills = Skill::where('is_active', 1)->select('id', 'name')->get();
            $topics = Topic::select('id', 'name', 'skill_id')->get();
            $difficultyLevels = DifficultyLevel::all();
            $passages = ComprehensionPassage::select('id', 'title')->get();

            // Using Repository to get Defaults (Old Logic)
            $defaultOptions = $this->repository->setDefaultOptions($questionType->code);
            $defaultPreferences = $this->repository->setDefaultPreferences($questionType->code);

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.create' : 'admin.questions.create';

            return view($viewPath, compact(
                'questionType',
                'skills',
                'topics',
                'difficultyLevels',
                'passages',
                'defaultOptions',
                'defaultPreferences'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid Question Type');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'question' => 'required',
            'skill_id' => 'required',
            'question_type_id' => 'required|exists:question_types,id',
            'default_marks' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['_token', 'question_image', 'options', 'attachment_options', 'last_active_tab', 'comprehension_id']);

            // Handle Nulls
            $data['topic_id'] = $request->topic_id ?: null;
            $data['difficulty_level_id'] = $request->difficulty_level_id ?: null;
            $data['default_time'] = $request->default_time ?: null;

            // Handle Image
            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $filename = time() . '_q_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/questions'), $filename);
                $data['question'] = $data['question'] . '<br><img src="' . asset('uploads/questions/' . $filename) . '" class="img-fluid" style="max-height: 300px;">';
            }

            // --- CORE LOGIC USING HELPER & REPOSITORY ---
            $questionType = QuestionType::findOrFail($request->question_type_id);
            $typeCode = $questionType->code;

            $data['options'] = $request->input('options', []);
            $data['preferences'] = $request->input('preferences', []);

            // FIB Logic using Helper (Same as Old Code)
            if ($typeCode === 'FIB') {
                $data['correct_answer'] = getBlankItems($data['question']);
            } elseif ($typeCode === 'MMA' || $typeCode === 'MMS' || $typeCode === 'MTF' || $typeCode === 'ORD' || $typeCode === 'SAQ') {
                $data['correct_answer'] = null; // Stored in options or calculated dynamically
            } else {
                // MSA, TOF
                $data['correct_answer'] = $request->input('correct_answer');
            }

            // Attachment Logic
            if ($request->has_attachment == 1) {
                if ($request->attachment_type == 'comprehension') {
                    $data['comprehension_passage_id'] = $request->comprehension_id ?: null;
                    $data['attachment_options'] = null;
                } else {
                    $data['comprehension_passage_id'] = null;
                    $data['attachment_options'] = $request->input('attachment_options');
                }
            } else {
                $data['has_attachment'] = 0;
                $data['comprehension_passage_id'] = null;
                $data['attachment_options'] = null;
            }

            if (Auth::check()) $data['created_by'] = Auth::id();
            if (!isset($data['is_active'])) $data['is_active'] = 1;

            Question::create($data);

            DB::commit();
            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.index')->with('success', 'Question created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Store Error: ' . $e->getMessage());
            return back()->with('error', 'System Error: ' . $e->getMessage())->withInput();
        }
    }



    public function edit(Request $request, $id): View|RedirectResponse
    {
        // try {  <-- COMMENT THIS LINE
        $question = Question::findOrFail($id);
        $this->authorizeInstructor($question);

        $questionType = $question->questionType;
        $skills = Skill::where('is_active', 1)->select('id', 'name')->get();
        $topics = Topic::select('id', 'name', 'skill_id')->get();
        $difficultyLevels = DifficultyLevel::all();
        $passages = ComprehensionPassage::select('id', 'title')->get();

        $steps = $this->repository->getSteps($question->id, 'details');

        $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.edit' : 'admin.questions.edit';

        return view($viewPath, compact(
            'question',
            'questionType',
            'skills',
            'topics',
            'difficultyLevels',
            'passages',
            'steps'
        ));

        // } catch (\Exception $e) {       <-- COMMENT THIS BLOCK
        //    Log::error('Edit Page Error: ' . $e->getMessage());
        //    dd($e->getMessage()); // <-- ADD THIS TO SEE ERROR ON SCREEN
        //    return redirect()->back()->with('error', 'Unable to load edit page.');
        // }
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'question' => 'required',
            'skill_id' => 'required',
            'default_marks' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $data = $request->except(['_token', '_method', 'options', 'attachment_options', 'last_active_tab', 'comprehension_id']);

            $data['topic_id'] = $request->topic_id ?: null;
            $data['difficulty_level_id'] = $request->difficulty_level_id ?: null;
            $data['default_time'] = $request->default_time ?: null;

            $typeCode = $question->questionType->code;
            $data['options'] = $request->input('options', []);
            $data['preferences'] = $request->input('preferences', []);

            // FIB Logic using Helper (Same as Old Code)
            if ($typeCode === 'FIB') {
                $data['correct_answer'] = getBlankItems($data['question']);
            } elseif ($typeCode === 'MTF' || $typeCode === 'ORD' || $typeCode === 'SAQ' || $typeCode === 'MMA') {
                $data['correct_answer'] = null;
            } else {
                $data['correct_answer'] = $request->input('correct_answer');
            }

            if ($request->has_attachment == 1) {
                if ($request->attachment_type == 'comprehension') {
                    $data['comprehension_passage_id'] = $request->comprehension_id ?: null;
                    $data['attachment_options'] = null;
                } else {
                    $data['comprehension_passage_id'] = null;
                    $data['attachment_options'] = $request->input('attachment_options');
                }
            } else {
                $data['has_attachment'] = 0;
                $data['comprehension_passage_id'] = null;
                $data['attachment_options'] = null;
            }

            if (!isset($data['is_active'])) $data['is_active'] = 0;

            $question->update($data);

            DB::commit();
            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'instructor.';
            return redirect()->route($routePrefix . 'questions.index')->with('success', 'Question updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update Error: ' . $e->getMessage())->withInput();
        }
    }

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
            return back()->with('success', 'Question deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting question.'], 500);
            }
            return back()->with('error', 'Error deleting question.');
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:questions,id'
            ]);

            $count = count($request->ids);

            Question::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} questions deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting items.'], 500);
        }
    }

    public function preview($id): View|string
    {
        try {
            $question = Question::with(['questionType', 'skill', 'topic', 'difficultyLevel', 'section'])->findOrFail($id);
            $this->authorizeInstructor($question);

            return view('admin.questions.partials.preview', compact('question'));
        } catch (\Exception $e) {
            Log::error('Preview Error: ' . $e->getMessage());
            return '<div class="p-4 text-red-500">Error loading preview.</div>';
        }
    }

    public function approve($id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            if (!Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized action.');
            }
            $question = Question::findOrFail($id);
            $question->update(['is_active' => true]);
            DB::commit();
            return redirect()->back()->with('success', 'Question approved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Unable to approve question.');
        }
    }

    public function pending(): View|RedirectResponse
    {
        try {
            if (!Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized.');
            }
            $questions = Question::with(['questionType', 'skill', 'topic', 'creator'])
                ->where('is_active', false)
                ->latest()
                ->paginate(10);
            return view('admin.questions.pending', compact('questions'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 'Unable to load pending questions.');
        }
    }

    public function usage($id): View
    {
        try {
            $question = Question::with(['linkedExams', 'skill', 'topic', 'questionType'])->findOrFail($id);
            $this->authorizeInstructor($question);
            $viewName = request()->routeIs('instructor.*') ? 'instructor.questions.usage' : 'admin.questions.usage';
            return view($viewName, compact('question'));
        } catch (\Exception $e) {
            abort(404, 'Question not found.');
        }
    }

    private function authorizeInstructor(Question $question): void
    {
        if (Auth::user()->hasRole('instructor') && $question->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this question.');
        }
    }
}
