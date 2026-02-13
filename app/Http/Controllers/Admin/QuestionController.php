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
use App\Repositories\QuestionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    private QuestionRepository $repository;

    /**
     * QuestionController constructor.
     * @param QuestionRepository $repository
     */
    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Helper to determine Route Prefix (admin. or panel.)
     * @return string
     */
    private function getRoutePrefix(): string
    {
        return Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
    }

    /**
     * Helper to get Route Params (role)
     * @return array
     */
    private function getRouteParams(): array
    {
        if (Auth::user()->hasRole('admin')) {
            return [];
        }
        return ['role' => request()->route('role') ?? 'instructor'];
    }

    /**
     * Authorize Instructor Ownership
     * @param Question $question
     */
    private function authorizeInstructor(Question $question): void
    {
        // 1. Admin Bypass
        if (Auth::user()->hasRole('admin') || Auth::user()->can('manage questions')) {
            return;
        }

        // 2. Ownership Check
        if (Auth::user()->hasRole('instructor') && $question->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this question.');
        }
    }

    /**
     * Display a listing of questions.
     */
    public function index(QuestionFilters $filters, Request $request): View|string
    {
        $query = Question::filter($filters)
            ->with(['questionType', 'skill', 'topic.skill.microCategory.subCategory', 'difficultyLevel', 'creator'])
            ->latest();

        if (Auth::user()->hasRole('instructor')) {
            $query->where('created_by', Auth::id());
        }

        $questions = $query->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return view('admin.questions.partials.questions-table', compact('questions'))->render();
        }

        $types = QuestionType::where('is_active', 1)->get();
        $skills = Skill::where('is_active', 1)->select('id', 'name')->get();

        return view('admin.questions.index', compact('questions', 'types', 'skills'));
    }

    /**
     * Show the form for creating a new question.
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

            $defaultOptions = $this->repository->setDefaultOptions($questionType->code);
            $defaultPreferences = $this->repository->setDefaultPreferences($questionType->code);

            // --- PRE-FILL LOGIC (For Bulk Creation) ---
            $prefill = [
                'skill_id' => $request->get('skill_id'),
                'topic_id' => $request->get('topic_id'),
                'comprehension_passage_id' => $request->get('comprehension_passage_id'),
            ];

            return view('admin.questions.create', compact(
                'questionType',
                'skills',
                'topics',
                'difficultyLevels',
                'passages',
                'defaultOptions',
                'defaultPreferences',
                'prefill' // Pass prefill data
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid Question Type');
        }
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $typeId = $request->input('question_type_id');
        $qType = QuestionType::find($typeId);
        $typeCode = $qType ? $qType->code : 'MSA';

        // Validation Rules
        $rules = [
            'question' => 'required',
            'skill_id' => 'required',
            'question_type_id' => 'required|exists:question_types,id',
            'default_marks' => 'required|numeric|min:0',
        ];

        // ISSUE #4 FIX: MMA/MTF validation bypass for 'correct_answer' field
        if (!in_array($typeCode, ['FIB', 'MMA', 'MTF', 'ORD', 'SAQ'])) {
            $rules['correct_answer'] = 'required';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $data = $request->except(['_token', 'question_image', 'options', 'attachment_options', 'last_active_tab', 'comprehension_id', 'submit_action']);

            $data['topic_id'] = $request->topic_id ?: null;
            $data['difficulty_level_id'] = $request->difficulty_level_id ?: null;
            $data['default_time'] = $request->default_time ?: null;

            // ISSUE #5 FIX: Native Image Upload Handling
            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $filename = time() . '_q_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/questions'), $filename);
                $imageUrl = asset('uploads/questions/' . $filename);
                // Append image to question content
                $data['question'] = $request->question . '<br><img src="' . $imageUrl . '" class="img-fluid rounded mt-2" style="max-height: 300px;">';
            }


            $options = $request->input('options', []);

            // OPTION IMAGE UPLOAD HANDLING
            foreach ($options as $index => $option) {
                // Check if this option has an uploaded image file
                $imageFieldName = "options.{$index}.image";
                if ($request->hasFile($imageFieldName)) {
                    $file = $request->file($imageFieldName);
                    $filename = time() . '_opt_' . $index . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/questions/options'), $filename);
                    // Store the relative path in the options array
                    $options[$index]['image'] = 'uploads/questions/options/' . $filename;
                }
            }

            $data['options'] = $options;
            $data['preferences'] = $request->input('preferences', []);

            // ISSUE #1 FIX: FIB & MMA Logic
            if ($typeCode === 'FIB') {
                $data['correct_answer'] = getBlankItems($request->question);
            } elseif ($typeCode === 'MMA') {
                $corrects = [];
                foreach ($options as $idx => $opt) {
                    if (isset($opt['is_correct']) && ($opt['is_correct'] == "1" || $opt['is_correct'] == "on")) {
                        $corrects[] = $idx;
                    }
                }
                $data['correct_answer'] = $corrects;
            } elseif (in_array($typeCode, ['MTF', 'ORD', 'SAQ'])) {
                $data['correct_answer'] = null;
            } else {
                $data['correct_answer'] = $request->input('correct_answer');
            }

            // Attachment logic
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
            if (!isset($data['is_active'])) $data['is_active'] = Auth::user()->hasRole('admin') ? 1 : 0;

            Question::create($data);
            DB::commit();

            // --- REDIRECT LOGIC ---
            if ($request->submit_action === 'save_and_add') {
                $params = $this->getRouteParams();
                $params['type'] = $typeCode;
                // Preserve Context
                $params['skill_id'] = $request->skill_id;
                $params['topic_id'] = $request->topic_id;
                $params['comprehension_passage_id'] = $request->comprehension_id;

                return redirect()->route($this->getRoutePrefix() . 'questions.create', $params)
                                 ->with('success', 'Question saved! Ready for next one.');
            }

            return redirect()->route($this->getRoutePrefix() . 'questions.index', $this->getRouteParams())
                             ->with('success', 'Question created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question Store Error: ' . $e->getMessage());
            return back()->with('error', 'System Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $id = $request->route('question');
        $question = Question::findOrFail($id);

        $this->authorizeInstructor($question);

        $questionType = $question->questionType;
        $skills = Skill::where('is_active', 1)->select('id', 'name')->get();
        $topics = Topic::select('id', 'name', 'skill_id')->get();
        $difficultyLevels = DifficultyLevel::all();
        $passages = ComprehensionPassage::select('id', 'title')->get();

        $steps = $this->repository->getSteps($question->id, 'details');

        return view('admin.questions.edit', compact(
            'question', 'questionType', 'skills', 'topics', 'difficultyLevels', 'passages', 'steps'
        ));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $id = $request->route('question');
        $question = Question::findOrFail($id);
        $this->authorizeInstructor($question);

        $request->validate([
            'question' => 'required',
            'skill_id' => 'required',
            'default_marks' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['_token', '_method', 'question_image', 'options', 'attachment_options', 'last_active_tab', 'comprehension_id']);

            $data['topic_id'] = $request->topic_id ?: null;
            $data['difficulty_level_id'] = $request->difficulty_level_id ?: null;
            $data['default_time'] = $request->default_time ?: null;

            if ($request->hasFile('question_image')) {
                $file = $request->file('question_image');
                $filename = time() . '_q_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/questions'), $filename);
                $data['question'] = $request->question . '<br><img src="' . asset('uploads/questions/' . $filename) . '" class="img-fluid rounded">';
            }


            $typeCode = $question->questionType->code;
            $options = $request->input('options', []);

            // OPTION IMAGE UPLOAD HANDLING
            foreach ($options as $index => $option) {
                // Check if this option has an uploaded image file
                $imageFieldName = "options.{$index}.image";
                if ($request->hasFile($imageFieldName)) {
                    $file = $request->file($imageFieldName);
                    $filename = time() . '_opt_' . $index . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/questions/options'), $filename);
                    // Store the relative path in the options array
                    $options[$index]['image'] = 'uploads/questions/options/' . $filename;
                }
                // If no new image uploaded, preserve existing image path if it exists
                elseif (isset($question->options[$index]['image']) && !empty($question->options[$index]['image'])) {
                    $options[$index]['image'] = $question->options[$index]['image'];
                }
            }

            $data['options'] = $options;
            $data['preferences'] = $request->input('preferences', []);

            if ($typeCode === 'FIB') {
                $data['correct_answer'] = getBlankItems($request->question);
            } elseif ($typeCode === 'MMA') {
                $corrects = [];
                foreach ($options as $idx => $opt) {
                    if (isset($opt['is_correct']) && ($opt['is_correct'] == "1" || $opt['is_correct'] == "on")) {
                        $corrects[] = $idx;
                    }
                }
                $data['correct_answer'] = $corrects;
            } elseif (in_array($typeCode, ['MTF', 'ORD', 'SAQ'])) {
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

            $question->update($data);
            DB::commit();

            // Handle "Save & Add Another" from Edit Screen
            if ($request->input('submit_action') == 'save_and_add') {
                $params = $this->getRouteParams();
                // Persist Context
                $params['skill_id'] = $question->skill_id;
                $params['topic_id'] = $question->topic_id;
                if ($question->comprehension_passage_id) {
                    $params['comprehension_passage_id'] = $question->comprehension_passage_id;
                }

                return redirect()->route($this->getRoutePrefix() . 'questions.create', $params)
                                 ->with('success', 'Question updated. detailed preserved for next question.');
            }

            return redirect()->route($this->getRoutePrefix() . 'questions.index', $this->getRouteParams())
                             ->with('success', 'Question updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle Question Status
     */
    public function toggleStatus(Request $request): RedirectResponse
    {
        $id = $request->route('id') ?? $request->route('question');

        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);
            $question->update(['is_active' => !$question->is_active]);
            return back()->with('success', 'Status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to update status.');
        }
    }

    /**
     * Remove the specified question from storage.
     */
     /**
     * Remove the specified question from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->route('question');

        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);

            // --- NEW CODE START: Check Logic ---
            // Check if question is linked to any exams
            if ($question->exams()->exists()) {
                // Get list of exam names (Assuming column name is 'title')
                $linkedExams = $question->exams->pluck('title')->take(5)->toArray();
                $totalLinked = $question->exams->count();

                if ($totalLinked > 5) {
                    $linkedExams[] = '...and ' . ($totalLinked - 5) . ' more';
                }

                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'linked', // Special status flag
                        'message' => 'Cannot delete linked question.',
                        'exams'   => $linkedExams
                    ]);
                }
                return back()->with('error', 'Cannot delete. Question is linked to active exams.');
            }
            // --- NEW CODE END ---

            $question->delete();
            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Question deleted.']);
            }

            return redirect()->route($this->getRoutePrefix() . 'questions.index', $this->getRouteParams())
                             ->with('success', 'Question deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting question.'], 500);
            }
            return back()->with('error', 'Error deleting question.');
        }
    }

    public function destroy_old(Request $request)
    {
        $id = $request->route('question');

        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $this->authorizeInstructor($question);
            $question->delete();
            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Question deleted.']);
            }

            return redirect()->route($this->getRoutePrefix() . 'questions.index', $this->getRouteParams())
                             ->with('success', 'Question deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting question.'], 500);
            }
            return back()->with('error', 'Error deleting question.');
        }
    }

    /**
     * Bulk Delete Questions
     */
     /**
     * Bulk Delete Questions with Validation
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:questions,id'
            ]);

            // 1. Get Questions (Security check included)
            $query = Question::whereIn('id', $request->ids)->with('exams'); // Eager load exams

            if (Auth::user()->hasRole('instructor')) {
                $query->where('created_by', Auth::id());
            }

            $questions = $query->get();

            $deletedCount = 0;
            $failedItems = []; // List of items that couldn't be deleted

            DB::beginTransaction();
            foreach ($questions as $q) {
                // 2. Check Linking
                if ($q->exams()->exists()) {
                    // Collect details for the popup
                    $examNames = $q->exams->pluck('title')->take(3)->toArray(); // Top 3 exams
                    if ($q->exams->count() > 3) {
                        $examNames[] = '...';
                    }

                    $failedItems[] = [
                        'code' => $q->code, // Question Code
                        'exams' => implode(', ', $examNames)
                    ];
                } else {
                    // 3. Delete Safe Items
                    $q->delete();
                    $deletedCount++;
                }
            }
            DB::commit();

            // 4. Return Smart Response
            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'failed_items' => $failedItems, // Yeh frontend pe dikhayenge
                'total_requested' => count($request->ids)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error processing request.'], 500);
        }
    }
    public function bulkDestroy_old(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:questions,id'
            ]);

            if (Auth::user()->hasRole('instructor')) {
                $count = Question::whereIn('id', $request->ids)
                                ->where('created_by', Auth::id())
                                ->delete();
            } else {
                $count = Question::whereIn('id', $request->ids)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} questions deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting items.'], 500);
        }
    }

    /**
     * Preview question content.
     */
    public function preview(Request $request): View|string
    {
        $id = $request->route('question');
        try {
            $question = Question::with(['questionType', 'skill', 'topic', 'difficultyLevel', 'creator']) // Removed 'section' - deprecated relationship
                                ->findOrFail($id);
            $this->authorizeInstructor($question);
            return view('admin.questions.partials.preview', compact('question'));
        } catch (\Exception $e) {
            Log::error('Preview Error: ' . $e->getMessage());
            return '<div class="p-4 text-red-500">Error loading preview.</div>';
        }
    }

    /**
     * Show detailed question info.
     */
    public function show(Request $request): View
    {
        $id = $request->route('question');
        $question = Question::findOrFail($id);
        $this->authorizeInstructor($question);
        return view('admin.questions.partials.preview', compact('question'));
    }

    /**
     * Show where the question is used.
     */
    public function usage(Request $request): View
    {
        $id = $request->route('id') ?? $request->route('question');
        try {
            $question = Question::with(['exams', 'skill', 'topic', 'questionType'])
                                ->findOrFail($id);
            $this->authorizeInstructor($question);
            return view('admin.questions.usage', compact('question'));
        } catch (\Exception $e) {
            abort(404, 'Question not found.');
        }
    }

    /**
     * Admin Approve Question
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
            return redirect()->back()->with('success', 'Question approved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Unable to approve question.');
        }
    }

    /**
     * List Pending Questions for Approval
     */
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
}
