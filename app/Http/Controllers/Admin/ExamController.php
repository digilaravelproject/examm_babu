<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Topic;
use App\Models\SubCategory;
use App\Models\MicroCategory; // Added Model
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ExamController extends Controller
{
    protected $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    // --- STEP 1: LIST & SEARCH ---

    public function index(Request $request)
    {
        $query = Exam::query();

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
            $is_active = $request->status === 'published' ? 1 : 0;
            $query->where('is_active', $is_active);
        }

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        // Added microCategory to eager loading
        $exams = $query->with(['subCategory', 'examType', 'microCategory', 'topic'])
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

    public function duplicate(Exam $exam)
    {
        DB::beginTransaction();

        try {
            /* ----------------------------
            * 1. Generate unique title
            * ---------------------------- */
            $baseTitle = $exam->title . '_copy';
            $newTitle  = $baseTitle;
            $counter   = 1;

            while (Exam::where('title', $newTitle)->exists()) {
                $newTitle = $baseTitle . '_' . $counter++;
            }

            /* ----------------------------
            * 2. Duplicate Exam
            * ---------------------------- */
            $newExam = $exam->replicate();
            $newExam->title = $newTitle;
            $newExam->code = 'EX-' . strtoupper(Str::random(8));
            $newExam->is_active = 0;
            $newExam->created_at = now();
            $newExam->updated_at = now();
            $newExam->save();

            /* ----------------------------
            * 3. Copy Exam Settings
            * ---------------------------- */
            if (!empty($exam->settings)) {
                $newExam->settings = $exam->settings;
                $newExam->save();
            }

            /* ----------------------------
            * 4. Duplicate Exam Sections
            * ---------------------------- */
            $sectionMap = []; // old_section_id => new_section_id

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

            /* ----------------------------
            * 5. DUPLICATE exam_questions TABLE
            * ---------------------------- */
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

            /* ----------------------------
            * 6. DUPLICATE exam_schedules TABLE
            * ---------------------------- */
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

            return redirect()
                ->route('admin.exams.index')
                ->with('success', 'Exam duplicated successfully with questions & schedules.');

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

        if ($request->filled('type')) {
            $query->where('exam_type_id', $request->type);
        }
        if ($request->filled('visibility')) {
            $query->where('is_private', $request->visibility);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
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
        $subCategories = SubCategory::where('is_active', 1)->get();
        $topics = Topic::where('is_active', 1)->get();

        // Fetch all active micro categories to pass to the view for dynamic JS filtering
        $microCategories = MicroCategory::where('is_active', 1)->get(['id', 'name', 'sub_category_id']);

        return view('admin.exams.create', compact('exam', 'examTypes', 'subCategories', 'steps', 'microCategories', 'topics'));
    }

    public function store(Request $request)
    {
        // 1. Validate
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'topic_id' => 'nullable|exists:topics,id',
            'micro_category_id' => 'nullable|exists:micro_categories,id', // Added Nullable Validation
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
            'status' => 'required|in:published,draft',
            'can_redeem' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // 2. Transform
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';
            $data['is_active'] = $request->status === 'published';
            $data['code'] = 'EX-' . strtoupper(Str::random(8));

            // 3. Cleanup
            unset($data['pricing_type'], $data['visibility'], $data['status']);

            // 4. Create
            $exam = Exam::create($data);

            DB::commit();
            return redirect()->route('admin.exams.settings', $exam->id)
                ->with('success', 'Exam Details Saved. Please configure settings.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Error creating exam: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $exam = Exam::findOrFail($id);
        $examTypes = ExamType::where('is_active', 1)->get();

        // $subCategories = SubCategory::where('id', $exam->sub_category_id)
        //     ->orWhere('category_id', $exam->subCategory->category_id ?? 0)
        //     ->get();
        $subCategories = SubCategory::where('is_active', 1)->get();


        // Fetch all active micro categories
        $microCategories = MicroCategory::where('is_active', 1)->get(['id', 'name', 'sub_category_id']);

        $steps = $this->repository->getSteps($exam->id, 'details');

        $topics = Topic::where('is_active', 1)->get();

        return view('admin.exams.edit', compact('exam', 'examTypes', 'subCategories', 'steps', 'microCategories', 'topics'));
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        // 1. Validate
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'topic_id' => 'nullable|exists:topics,id',
            'micro_category_id' => 'nullable|exists:micro_categories,id', // Added Nullable Validation
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
            'status' => 'required|in:published,draft',
            'can_redeem' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // 2. Transform
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';
            $data['is_active'] = $request->status === 'published';

            // 3. Cleanup
            unset($data['pricing_type'], $data['visibility'], $data['status']);

            // 4. Update
            $exam->update($data);

            DB::commit();
            return redirect()->route('admin.exams.settings', $exam->id)
                ->with('success', 'Exam Details Updated. Proceed to Settings.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // --- STEP 3: SETTINGS (No Changes Required) ---
    public function settings($id)
    {
        $exam = Exam::findOrFail($id);
        $steps = $this->repository->getSteps($exam->id, 'settings');

        $settings = [
            'auto_duration' => $exam->settings->get('auto_duration', true),
            'auto_grading' => $exam->settings->get('auto_grading', true),
            'cutoff' => $exam->settings->get('cutoff', 60),
            'enable_section_cutoff' => $exam->settings->get('enable_section_cutoff', false),
            'enable_negative_marking' => $exam->settings->get('enable_negative_marking', false),
            'restrict_attempts' =>  $exam->settings->get('restrict_attempts', false),
            'no_of_attempts' => $exam->settings->get('no_of_attempts', null),
            'disable_section_navigation' => $exam->settings->get('disable_section_navigation', false),
            'disable_question_navigation' => $exam->settings->get('disable_question_navigation', false),
            'disable_finish_button' => $exam->settings->get('disable_finish_button', false),
            'hide_solutions' => $exam->settings->get('hide_solutions', false),
            'list_questions' => $exam->settings->get('list_questions', true),
            'shuffle_questions' => $exam->settings->get('shuffle_questions', false),
            'show_leaderboard' => $exam->settings->get('show_leaderboard', true),
            'duration_mode' => $exam->settings->get('duration_mode', 'auto'),
            'marks_mode' => $exam->settings->get('marks_mode', 'auto'),
        ];

        return view('admin.exams.settings', compact('exam', 'steps', 'settings'));
    }

    public function updateSettings(Request $request, $id)
    {
        $exam = Exam::with('examSections')->findOrFail($id);
        // ... (Existing logic remains same) ...
        $booleanKeys = ['auto_duration', 'auto_grading', 'enable_negative_marking', 'enable_section_cutoff', 'shuffle_questions', 'restrict_attempts', 'disable_section_navigation', 'disable_finish_button', 'disable_question_navigation', 'list_questions', 'hide_solutions', 'show_leaderboard'];
        $valueKeys = ['cutoff', 'no_of_attempts', 'duration_mode', 'marks_mode'];
        $newSettings = [];
        foreach ($booleanKeys as $key) $newSettings[$key] = $request->has($key);
        foreach ($valueKeys as $key) $newSettings[$key] = $request->input($key);

        DB::beginTransaction();
        try {
            $exam->settings = $newSettings;
            $exam->save();
            foreach ($exam->examSections as $examSection) if (method_exists($examSection, 'updateMeta')) $examSection->updateMeta();
            if (method_exists($exam, 'updateMeta')) $exam->updateMeta();
            DB::commit();
            return redirect()->route('admin.exams.sections.index', ['exam' => $exam->id])->with('success', 'Exam Settings Updated.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Failed to save settings.');
        }
    }

    // --- STEP 4: DELETE (No Changes Required) ---
    public function destroy($id)
    {
        try {
            $exam = Exam::findOrFail($id);
            DB::transaction(function () use ($exam) {
                if (method_exists($exam, 'examSchedules')) $exam->examSchedules()->forceDelete();
                if (method_exists($exam, 'sessions')) $exam->sessions()->forceDelete();
                if (method_exists($exam, 'questions')) $exam->questions()->detach();
                if (method_exists($exam, 'examSections')) $exam->examSections()->forceDelete();
                if (method_exists($exam, 'secureDelete')) $exam->secureDelete('examSections', 'sessions', 'examSchedules');
                else $exam->delete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.exams.index')->with('error', 'Unable to Delete Exam. Remove all associations and try again!');
        }
        return redirect()->route('admin.exams.index')->with('success', 'Exam was successfully deleted!');
    }
}
