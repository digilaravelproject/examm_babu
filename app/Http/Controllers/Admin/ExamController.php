<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Topic;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\MicroCategory;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ExamSession;
use Exception;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    protected $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    // --- HELPER METHODS ---

    private function getRoutePrefix(): string
    {
        return Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
    }

    /**
     * Helper: Get Route Params (Fix for Missing Parameter: role)
     */
    private function getRouteParams(): array
    {
        if (Auth::user()->hasRole('admin')) {
            return [];
        }
        return ['role' => request()->route('role') ?? 'instructor'];
    }

    private function authorizeInstructor(Exam $exam): void
    {
        if (Auth::user()->hasRole('instructor') && $exam->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this exam.');
        }
    }

    // --- STEP 1: LIST & SEARCH ---

    public function index(Request $request)
    {
        $query = Exam::query();

        // 1. Instructor Filter
        if (Auth::user()->hasRole('instructor')) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('exam_type_id', $request->type);
        }

        if ($request->filled('status')) {
            $status = $request->status;

            // Handle new statuses (draft, pending, published, rejected)
            if (in_array($status, [Exam::STATUS_DRAFT, Exam::STATUS_PENDING, Exam::STATUS_PUBLISHED, Exam::STATUS_REJECTED])) {
                $query->where('status', $status);
            }
            // Backward compatibility for is_active filters (if needed)
            elseif ($status === 'active') {
                $query->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        // Added 'creator.roles' to eager loading for Admin View
        $exams = $query->with([
                'category',
                'subCategory',
                'examType',
                'microCategory',
                'topic.skill.microCategory.subCategory',
                'creator.roles'
            ])
            ->withCount(['examSections'])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends($request->all());

        if ($request->ajax()) {
            return view('admin.exams.partials.table', compact('exams'))->render();
        }

        $examTypes = ExamType::where('is_active', 1)->get();
        $topics = Topic::where('is_active', 1)->get();

        return view('admin.exams.index', compact('exams', 'examTypes', 'topics'));
    }

    public function duplicate(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);

        $this->authorizeInstructor($exam);

        DB::beginTransaction();

        try {
            // 1. Generate unique title
            $baseTitle = $exam->title . '_copy';
            $newTitle  = $baseTitle;
            $counter   = 1;

            while (Exam::where('title', $newTitle)->exists()) {
                $newTitle = $baseTitle . '_' . $counter++;
            }

            // 2. Duplicate Exam
            $newExam = $exam->replicate();
            $newExam->title = $newTitle;
            $newExam->code = 'EX-' . strtoupper(Str::random(8));

            // Logic: Cloned exams are always DRAFT
            $newExam->status = Exam::STATUS_DRAFT;
            $newExam->is_active = 0;

            $newExam->created_at = now();
            $newExam->updated_at = now();
            $newExam->created_by = Auth::id();
            $newExam->save();

            // 3. Copy Exam Settings
            if (!empty($exam->settings)) {
                $newExam->settings = $exam->settings;
                $newExam->save();
            }

            // 4. Duplicate Exam Sections
            $sectionMap = [];

            foreach ($exam->examSections as $section) {
                $newSection = $section->replicate();
                $newSection->exam_id = $newExam->id;
                $newSection->created_at = now();
                $newSection->updated_at = now();
                $newSection->save();

                $sectionMap[$section->id] = $newSection->id;

                if (method_exists($newSection, 'updateMeta')) {
                    $newSection->updateMeta();
                }
            }

            // 5. DUPLICATE exam_questions TABLE
            $examQuestions = DB::table('exam_questions')
                ->where('exam_id', $exam->id)
                ->get();

            foreach ($examQuestions as $row) {
                DB::table('exam_questions')->insert([
                    'exam_id'         => $newExam->id,
                    'question_id'     => $row->question_id,
                    'exam_section_id' => $sectionMap[$row->exam_section_id] ?? null,
                ]);
            }

            // 6. DUPLICATE exam_schedules TABLE
            $examSchedules = DB::table('exam_schedules')
                ->where('exam_id', $exam->id)
                ->get();

            foreach ($examSchedules as $schedule) {
                DB::table('exam_schedules')->insert([
                    'code'          => 'SCH-' . strtoupper(Str::random(6)),
                    'exam_id'       => $newExam->id,
                    'schedule_type' => $schedule->schedule_type,
                    'start_date'    => $schedule->start_date,
                    'start_time'    => $schedule->start_time,
                    'end_date'      => $schedule->end_date,
                    'end_time'      => $schedule->end_time,
                    'grace_period'  => $schedule->grace_period,
                    'status'        => 'inactive',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::commit();

            // FIX: Dynamic Redirect with Params
            return redirect()
                ->route($this->getRoutePrefix() . 'exams.index', $this->getRouteParams())
                ->with('success', 'Exam duplicated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exam Duplicate Failed', [
                'exam_id' => $exam->id,
                'error'   => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to duplicate exam.');
        }
    }

    public function search(Request $request)
    {
        $searchQuery = $request->get('query');
        $query = Exam::select(['id', 'title']);

        if (Auth::user()->hasRole('instructor')) {
            $query->where('created_by', Auth::id());
        }

        if ($request->filled('type')) {
            $query->where('exam_type_id', $request->type);
        }
        if ($request->filled('visibility')) {
            $query->where('is_private', $request->visibility);
        }
        if ($request->filled('status')) {
             if (in_array($request->status, [Exam::STATUS_DRAFT, Exam::STATUS_PENDING, Exam::STATUS_PUBLISHED, Exam::STATUS_REJECTED])) {
                $query->where('status', $request->status);
            }
        }
        if ($searchQuery) {
            $query->where('title', 'like', '%' . $searchQuery . '%');
        }

        $exams = $query->limit(20)->get();
        return response()->json(['exams' => $exams]);
    }

    // --- STEP 2: DETAILS (CREATE / EDIT) ---

    public function create()
    {
        $exam = new Exam();
        $examTypes = ExamType::where('is_active', 1)->get();
        $steps = $this->repository->getSteps(null, 'details');
        $categories = Category::where('is_active', 1)->get();
        $subCategories = SubCategory::where('is_active', 1)->get(['id', 'name', 'category_id']);
        $microCategories = MicroCategory::where('is_active', 1)->get(['id', 'name', 'sub_category_id']);
        $topics = Topic::where('is_active', 1)->get();

        return view('admin.exams.create', compact('exam', 'examTypes', 'categories', 'subCategories', 'steps', 'microCategories', 'topics'));
    }

    public function store(Request $request)
    {
        // 1. Validate
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'topic_id' => 'nullable|exists:topics,id',
            'micro_category_id' => 'required|exists:micro_categories,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
            'status' => 'nullable|string', // Validation relaxed as we handle logic below
            'can_redeem' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // 2. Transform
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';
            $data['code'] = 'EX-' . strtoupper(Str::random(8));
            $data['created_by'] = Auth::id();

            // 🔥 LOGIC CHANGE: Role Based Status Handling
            if (Auth::user()->hasRole('admin')) {
                // Admin: Can set status directly
                $requestedStatus = $request->input('status', Exam::STATUS_DRAFT);

                if ($requestedStatus === 'published') {
                    $data['status'] = Exam::STATUS_PUBLISHED;
                    $data['is_active'] = 1;
                } else {
                    $data['status'] = Exam::STATUS_DRAFT;
                    $data['is_active'] = 0;
                }
            } else {
                // Instructor: Always Draft initially
                $data['is_active'] = 0; // Inactive
                $data['status'] = Exam::STATUS_DRAFT; // Draft
            }

            // 3. Cleanup
            unset($data['pricing_type'], $data['visibility']);

            // 4. Create
            $exam = Exam::create($data);

            DB::commit();

            // FIX: Dynamic Redirect with Params for Instructor Route
            $params = array_merge($this->getRouteParams(), ['exam' => $exam->id]);

            return redirect()->route($this->getRoutePrefix() . 'exams.settings', $params)
                ->with('success', 'Exam Details Saved. Please configure settings.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Error creating exam: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);
        $this->authorizeInstructor($exam);

        $examTypes = ExamType::where('is_active', 1)->get();
        $categories = Category::where('is_active', 1)->get();
        $subCategories = SubCategory::where('is_active', 1)->get(['id', 'name', 'category_id']);
        $microCategories = MicroCategory::where('is_active', 1)->get(['id', 'name', 'sub_category_id']);
        $steps = $this->repository->getSteps($exam->id, 'details');
        $topics = Topic::where('is_active', 1)->get();

        return view('admin.exams.edit', compact('exam', 'examTypes', 'categories', 'subCategories', 'steps', 'microCategories', 'topics'));
    }

    public function update(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);
        $this->authorizeInstructor($exam);

        // 1. Validate
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'topic_id' => 'nullable|exists:topics,id',
            'micro_category_id' => 'required|exists:micro_categories,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
            'status' => 'nullable|string',
            'can_redeem' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // 2. Transform
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';

            // 🔥 LOGIC CHANGE: Role Based Status Handling
            if (Auth::user()->hasRole('admin')) {
                // Admin: Can update status directly
                $requestedStatus = $request->input('status');

                if ($requestedStatus === 'published') {
                    $data['status'] = Exam::STATUS_PUBLISHED;
                    $data['is_active'] = 1;
                } elseif ($requestedStatus === 'draft') {
                    $data['status'] = Exam::STATUS_DRAFT;
                    $data['is_active'] = 0;
                }
                // If status not provided, keep existing
            } else {
                // Instructor: Do NOT update status here to prevent bypassing approval
                // Only content updates allowed
                unset($data['status']);
                unset($data['is_active']);
            }

            // 3. Cleanup
            unset($data['pricing_type'], $data['visibility']);

            // 4. Update
            $exam->update($data);

            DB::commit();

            // FIX: Dynamic Redirect with Params for Instructor Route
            $params = array_merge($this->getRouteParams(), ['exam' => $exam->id]);

            return redirect()->route($this->getRoutePrefix() . 'exams.settings', $params)
                ->with('success', 'Exam Details Updated. Proceed to Settings.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // --- STEP 3: SETTINGS ---

    public function settings(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);
        $this->authorizeInstructor($exam);

        $steps = $this->repository->getSteps($exam->id, 'settings');

        // Using ->get() on schemaless attributes
        $s = $exam->settings instanceof \Illuminate\Support\Collection ? $exam->settings : collect($exam->settings ?? []);

        $settings = [
            'auto_duration' => $s->get('auto_duration', true),
            'auto_grading' => $s->get('auto_grading', true),
            'cutoff' => $s->get('cutoff', 60),
            'enable_section_cutoff' => $s->get('enable_section_cutoff', false),
            'enable_negative_marking' => $s->get('enable_negative_marking', false),
            'restrict_attempts' =>  $s->get('restrict_attempts', false),
            'no_of_attempts' => $s->get('no_of_attempts', null),
            'disable_section_navigation' => $s->get('disable_section_navigation', false),
            'disable_question_navigation' => $s->get('disable_question_navigation', false),
            'disable_finish_button' => $s->get('disable_finish_button', false),
            'hide_solutions' => $s->get('hide_solutions', false),
            'list_questions' => $s->get('list_questions', true),
            'shuffle_questions' => $s->get('shuffle_questions', false),
            'show_leaderboard' => $s->get('show_leaderboard', true),
            'duration_mode' => $s->get('duration_mode', 'auto'),
            'marks_mode' => $s->get('marks_mode', 'auto'),
        ];

        return view('admin.exams.settings', compact('exam', 'steps', 'settings'));
    }

    public function updateSettings(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::with('examSections')->findOrFail($id);
        $this->authorizeInstructor($exam);

        $booleanKeys = [
            'auto_duration', 'auto_grading', 'enable_negative_marking', 'enable_section_cutoff',
            'shuffle_questions', 'restrict_attempts', 'disable_section_navigation', 'disable_finish_button',
            'disable_question_navigation', 'list_questions', 'hide_solutions', 'show_leaderboard'
        ];

        $valueKeys = ['cutoff', 'no_of_attempts', 'duration_mode', 'marks_mode'];

        $newSettings = [];

        foreach ($booleanKeys as $key) {
            $newSettings[$key] = $request->input($key) == '1';
        }

        foreach ($valueKeys as $key) {
            $newSettings[$key] = $request->input($key);
        }

        DB::beginTransaction();
        try {
            $rawSettings = $exam->settings;
            $currentSettings = [];

            if (is_array($rawSettings)) {
                $currentSettings = $rawSettings;
            } elseif (is_object($rawSettings)) {
                if (is_callable([$rawSettings, 'all'])) {
                    $currentSettings = $rawSettings->all();
                } elseif (is_callable([$rawSettings, 'toArray'])) {
                    $currentSettings = $rawSettings->toArray();
                } else {
                    $currentSettings = (array) $rawSettings;
                }
            }

            $exam->settings = array_merge($currentSettings, $newSettings);
            $exam->save();

            foreach ($exam->examSections as $examSection) {
                if (method_exists($examSection, 'updateMeta')) {
                    $examSection->updateMeta();
                }
            }

            if (method_exists($exam, 'updateMeta')) {
                $exam->updateMeta();
            }

            DB::commit();

            // FIX: Dynamic Redirect with Params for Instructor Route
            $params = array_merge($this->getRouteParams(), ['exam' => $exam->id]);

            return redirect()->route($this->getRoutePrefix() . 'exams.sections.index', $params)->with('success', 'Exam Settings Updated.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    // --- STEP 4: DELETE ---

    public function destroy(Request $request)
    {
        $id = $request->route('exam');
        try {
            $exam = Exam::findOrFail($id);
            $this->authorizeInstructor($exam);

            DB::transaction(function () use ($exam) {
                if (method_exists($exam, 'examSchedules')) $exam->examSchedules()->forceDelete();
                if (method_exists($exam, 'sessions')) $exam->sessions()->forceDelete();
                if (method_exists($exam, 'questions')) $exam->questions()->detach();
                if (method_exists($exam, 'examSections')) $exam->examSections()->forceDelete();

                if (method_exists($exam, 'secureDelete')) {
                    $exam->secureDelete('examSections', 'sessions', 'examSchedules');
                } else {
                    $exam->delete();
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // FIX: Dynamic Redirect with Params
            return redirect()->route($this->getRoutePrefix() . 'exams.index', $this->getRouteParams())->with('error', 'Unable to Delete Exam.');
        }

        // FIX: Dynamic Redirect with Params
        return redirect()->route($this->getRoutePrefix() . 'exams.index', $this->getRouteParams())->with('success', 'Exam was successfully deleted!');
    }

    // --- PREVIEW ---
    public function preview(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::with(['examSections.questions'])->findOrFail($id);
        $this->authorizeInstructor($exam);

        if ($exam->examSections->isEmpty()) {
            return back()->with('error', 'Cannot preview: This exam has no sections.');
        }

        // --- STEP 1: Shuffle Setting Check Karna ---
        // Hum wahi logic use karenge jo aapke settings() method mein hai
        $settings = $exam->settings instanceof \Illuminate\Support\Collection
            ? $exam->settings
            : collect($exam->settings ?? []);

        // Default false (0) rakha hai agar setting na mile
        $shouldShuffle = $settings->get('shuffle_questions', false);

        DB::beginTransaction();
        try {
            $sessionCode = 'PREVIEW-' . Str::upper(Str::random(10));

            // 0. Calculate Actual Duration (Robust Logic)
            // Priority 1: Stored Total Duration
            $totalDuration = $exam->total_duration;

            // Priority 2: Dynamic Section Sum
            if ($totalDuration == 0) {
                 $totalDuration = $exam->examSections()->sum('total_duration');
            }

            // Priority 3: Dynamic Question Sum (Auto Mode)
            if ($totalDuration == 0) {
                 $totalDuration = $exam->questions()->sum('default_time');
            }

            // Priority 4: Fallback
            if ($totalDuration == 0) {
                 $totalDuration = 86400; // 24 Hours
            }

            $session = ExamSession::create([
                'code' => $sessionCode,
                'user_id' => Auth::id(),
                'exam_id' => $exam->id,
                'exam_schedule_id' => null,
                'status' => 'started',
                'starts_at' => now(),
                'ends_at' => now()->addSeconds($totalDuration),
            ]);

            $sections = $exam->examSections()->orderBy('section_order', 'asc')->get();
            $examQuestions = $exam->questions()
                ->with('questionType')
                ->get()
                ->groupBy('pivot.exam_section_id');

            $sessionQuestionsData = [];
            $sessionSectionsData = [];
            $globalSno = 1;

            foreach ($sections as $section) {
                $sessionSectionsData[] = [
                    'exam_session_id' => $session->id,
                    'exam_section_id' => $section->id,
                    'section_id'      => !empty($section->section_id) ? $section->section_id : 0,
                    'name'            => $section->name,
                    'sno'             => $section->section_order,
                    'status'          => ($globalSno == 1) ? 'started' : 'not_visited',
                ];

                if (isset($examQuestions[$section->id])) {
                    $qList = $examQuestions[$section->id];

                    // --- STEP 2: Logic to SHUFFLE ---
                    // Agar setting on hai, to questions ka order randomize kar do
                    if ($shouldShuffle) {
                        $qList = $qList->shuffle();
                    }

                    $sectionQNo = 1;
                    foreach ($qList as $q) {
                        $sessionQuestionsData[] = [
                            'exam_session_id' => $session->id,
                            'question_id'     => $q->id,
                            'exam_section_id' => $section->id,
                            'sno'             => $sectionQNo++,
                            'original_question' => $q->question,
                            'options'         => is_array($q->options) ? json_encode($q->options) : $q->options,
                            'correct_answer'  => is_array($q->correct_answer) ? serialize($q->correct_answer) : $q->correct_answer,
                            'user_answer'     => null,
                            'status'          => 'not_visited',
                            'is_correct'      => 0,
                            'time_taken'      => 0,
                            'marks_earned'    => 0,
                            'marks_deducted'  => 0,
                        ];
                        $globalSno++;
                    }
                }
            }

            if (!empty($sessionSectionsData)) {
                DB::table('exam_session_sections')->insert($sessionSectionsData);
            }

            if (!empty($sessionQuestionsData)) {
                DB::table('exam_session_questions')->insert($sessionQuestionsData);
            }

            DB::commit();
            return redirect()->route('student.exam.interface', $session->code);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Preview Error: " . $e->getMessage());
            return back()->with('error', 'Failed to generate preview: ' . $e->getMessage());
        }
    }

    public function preview_old(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::with(['examSections.questions'])->findOrFail($id);
        $this->authorizeInstructor($exam);

        if ($exam->examSections->isEmpty()) {
            return back()->with('error', 'Cannot preview: This exam has no sections.');
        }

        DB::beginTransaction();
        try {
            $sessionCode = 'PREVIEW-' . Str::upper(Str::random(10));

            $session = ExamSession::create([
                'code' => $sessionCode,
                'user_id' => Auth::id(),
                'exam_id' => $exam->id,
                'exam_schedule_id' => null,
                'status' => 'started',
                'starts_at' => now(),
                'ends_at' => now()->addHours(24),
            ]);

            $sections = $exam->examSections()->orderBy('section_order', 'asc')->get();
            $examQuestions = $exam->questions()
                ->with('questionType')
                ->get()
                ->groupBy('pivot.exam_section_id');

            $sessionQuestionsData = [];
            $sessionSectionsData = [];
            $globalSno = 1;

            foreach ($sections as $section) {
                $sessionSectionsData[] = [
                    'exam_session_id' => $session->id,
                    'exam_section_id' => $section->id,
                    'section_id'      => !empty($section->section_id) ? $section->section_id : 0,
                    'name'            => $section->name,
                    'sno'             => $section->section_order,
                    'status'          => ($globalSno == 1) ? 'started' : 'not_visited',
                ];

                if (isset($examQuestions[$section->id])) {
                    $qList = $examQuestions[$section->id];
                    $sectionQNo = 1;
                    foreach ($qList as $q) {
                        $sessionQuestionsData[] = [
                            'exam_session_id' => $session->id,
                            'question_id'     => $q->id,
                            'exam_section_id' => $section->id,
                            'sno'             => $sectionQNo++,
                            'original_question' => $q->question,
                            'options'         => is_array($q->options) ? json_encode($q->options) : $q->options,
                            'correct_answer'  => is_array($q->correct_answer) ? serialize($q->correct_answer) : $q->correct_answer,
                            'user_answer'     => null,
                            'status'          => 'not_visited',
                            'is_correct'      => 0,
                            'time_taken'      => 0,
                            'marks_earned'    => 0,
                            'marks_deducted'  => 0,
                        ];
                        $globalSno++;
                    }
                }
            }

            if (!empty($sessionSectionsData)) {
                DB::table('exam_session_sections')->insert($sessionSectionsData);
            }

            if (!empty($sessionQuestionsData)) {
                DB::table('exam_session_questions')->insert($sessionQuestionsData);
            }

            DB::commit();
            return redirect()->route('student.exam.interface', $session->code);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Preview Error: " . $e->getMessage());
            return back()->with('error', 'Failed to generate preview: ' . $e->getMessage());
        }
    }

    // =========================================================
    //  APPROVAL WORKFLOW LOGIC
    // =========================================================

    /**
     * Instructor: Submit Exam for Review
     */
    public function submitForReview(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);

        $this->authorizeInstructor($exam);

        $request->validate([
            'submitter_note' => 'nullable|string|max:1000',
        ]);

        $exam->update([
            'status' => Exam::STATUS_PENDING,
            'submitter_note' => $request->submitter_note,
            'is_active' => 0
        ]);

        return redirect()->back()->with('success', 'Exam submitted for review successfully!');
    }

    /**
     * Admin: Approve or Reject Exam
     */
    public function statusAction(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($request->action === 'approve') {
            $exam->update([
                'status' => Exam::STATUS_PUBLISHED,
                'is_active' => 1,
                'admin_note' => $request->admin_note
            ]);
            $msg = 'Exam Approved and Published!';
        } else {
            $exam->update([
                'status' => Exam::STATUS_REJECTED,
                'is_active' => 0,
                'admin_note' => $request->admin_note
            ]);
            $msg = 'Exam Rejected and sent back to Instructor.';
        }

        return redirect()->back()->with('success', $msg);
    }

        public function quickPublish(Request $request)
    {
        $id = $request->route('exam');
        $exam = Exam::findOrFail($id);
        $this->authorizeInstructor($exam);

        $exam->update([
            'status' => 'published',
            'is_active' => 1
        ]);

        // Redirect to schedules index now that it is published
        return redirect()
            ->route($this->getRoutePrefix() . 'exams.schedules.index', array_merge($this->getRouteParams(), ['exam' => $exam->id]))
            ->with('success', 'Exam published successfully! You can now manage schedules.');
    }
}
