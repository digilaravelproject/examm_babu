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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|RedirectResponse
    {
        try {
            $typeCode = $request->get('type', 'MSA');
            $questionType = QuestionType::where('code', $typeCode)->firstOrFail();

            $skills = Skill::where('is_active', 1)->select('id', 'name')->get();
            $topics = Topic::select('id', 'name', 'skill_id')->get();
            $difficultyLevels = DifficultyLevel::all();
            $passages = ComprehensionPassage::select('id', 'title')->get();

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.create' : 'admin.questions.create';

            return view($viewPath, compact(
                'questionType',
                'skills',
                'topics',
                'difficultyLevels',
                'passages'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid Question Type');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation
        $request->validate([
            'question' => 'required',
            'skill_id' => 'required',
            'default_marks' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // FIX: Exclude non-column fields
            $data = $request->except(['_token', 'question_image', 'options', 'attachment_options', 'last_active_tab', 'comprehension_id']);

            // FIX: Handle Empty Strings / Nulls
            $data['topic_id'] = $request->topic_id ?: null;
            $data['difficulty_level_id'] = $request->difficulty_level_id ?: null;
            $data['default_time'] = $request->default_time ?: null;

            // 2. Handle Question Image
            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $filename = time() . '_q_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/questions'), $filename);
                $data['question'] = $data['question'] . '<br><img src="' . asset('uploads/questions/' . $filename) . '" class="img-fluid" style="max-height: 300px;">';
            }

            // 3. Logic per Question Type (Options)
            $questionType = QuestionType::findOrFail($request->question_type_id);
            $typeCode = $questionType->code;
            $options = $request->input('options', []);

            if ($typeCode === 'FIB') {
                preg_match_all('/##(.*?)##/', $data['question'], $matches);
                if (!empty($matches[1])) {
                    $options = array_map(function ($item) {
                        return ['option' => trim($item), 'is_correct' => true];
                    }, $matches[1]);
                    $data['correct_answer'] = $matches[1];
                }
            } elseif ($typeCode === 'MMA' || $typeCode === 'MMS') {
                $data['correct_answer'] = null; // Correctness inside options JSON
            } else {
                $options = array_values($options); // Reindex
            }

            $data['options'] = $options;

            // 4. Handle Attachments
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

            // 5. Create
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id): View|RedirectResponse
    {
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            $questionType = $question->questionType;
            $skills = Skill::where('is_active', 1)->select('id', 'name')->get();
            $topics = Topic::select('id', 'name', 'skill_id')->get();
            $difficultyLevels = DifficultyLevel::all();
            $passages = ComprehensionPassage::select('id', 'title')->get();

            $viewPath = request()->routeIs('instructor.*') ? 'instructor.questions.edit' : 'admin.questions.edit';

            return view($viewPath, compact(
                'question',
                'questionType',
                'skills',
                'topics',
                'difficultyLevels',
                'passages'
            ));
        } catch (\Exception $e) {
            Log::error('Edit Page Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load edit page.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
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
            $options = $request->input('options', []);

            if ($typeCode === 'FIB') {
                preg_match_all('/##(.*?)##/', $data['question'], $matches);
                if (!empty($matches[1])) {
                    $options = array_map(function ($item) {
                        return ['option' => trim($item), 'is_correct' => true];
                    }, $matches[1]);
                    $data['correct_answer'] = $matches[1];
                }
            } else {
                $options = array_values($options);
            }

            $data['options'] = $options;

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

    /**
     * Remove the specified resource from storage.
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
            return back()->with('success', 'Question deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting question.'], 500);
            }
            return back()->with('error', 'Error deleting question.');
        }
    }

    /**
     * Load Preview Modal Content.
     */
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

    /**
     * Approve a specific question (Admin Only).
     */
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
            return redirect()->back()->with('success', 'Question approved and is now live.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Approval Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to approve question.');
        }
    }

    /**
     * Show Pending Questions (Admin Only).
     */
    public function pending(): View|RedirectResponse
    {
        try {
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
     * Show Question Usage.
     */
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

    /**
     * Helper to authorize instructor access.
     */
    private function authorizeInstructor(Question $question): void
    {
        if (Auth::user()->hasRole('instructor') && $question->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this question.');
        }
    }
}
