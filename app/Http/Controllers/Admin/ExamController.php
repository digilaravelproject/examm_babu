<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\SubCategory;
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
        // $this->middleware(['role:admin|instructor'])->except('search');
        $this->repository = $repository;
    }

    // --- STEP 1: LIST & SEARCH (With Inline Filters) ---

    /**
     * List all exams with Direct Filtering
     */
    public function index(Request $request)
    {
        // 1. Start Query
        $query = Exam::query();

        // 2. Filters
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

        // 3. Get Results
        $exams = $query->with(['subCategory', 'examType'])
            ->withCount(['examSections'])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends($request->all());

        // 4. CHECK FOR AJAX (This fixes the recursion)
        if ($request->ajax()) {
            return view('admin.exams.partials.table', compact('exams'))->render();
        }

        // 5. Normal Page Load
        $examTypes = ExamType::where('is_active', 1)->get();
        return view('admin.exams.index', compact('exams', 'examTypes'));
    }

    /**
     * Search exams api endpoint
     */
    public function search(Request $request)
    {
        $searchQuery = $request->get('query');

        $query = Exam::select(['id', 'title']);

        // Apply same filters if passed via API
        if ($request->filled('type')) {
            $query->where('exam_type_id', $request->type);
        }
        if ($request->filled('visibility')) {
            $query->where('is_private', $request->visibility);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Apply the main search query on Title
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
        $subCategories = SubCategory::where('is_active', 1)->limit(20)->get();

        return view('admin.exams.create', compact('exam', 'examTypes', 'subCategories', 'steps'));
    }

   public function store(Request $request)
    {
        // 1. Validate all fields including the new ones
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
            'status' => 'required|in:published,draft', // New Field
            'can_redeem' => 'required|boolean',         // New Field (0 or 1)
        ]);

        DB::beginTransaction();
        try {
            // 2. Transform Form Dropdowns to Database Booleans
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';
            $data['is_active'] = $request->status === 'published'; // Published = 1, Draft = 0

            // Generate Code
            $data['code'] = 'EX-' . strtoupper(Str::random(8));

            // 3. Remove temporary form fields that don't exist in the database
            unset($data['pricing_type'], $data['visibility'], $data['status']);

            // 4. Create Exam
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

        $subCategories = SubCategory::where('id', $exam->sub_category_id)
            ->orWhere('category_id', $exam->subCategory->category_id ?? 0)
            ->get();

        $steps = $this->repository->getSteps($exam->id, 'details');

        return view('admin.exams.edit', compact('exam', 'examTypes', 'subCategories', 'steps'));
    }

   public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        // 1. Validate
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
            'status' => 'required|in:published,draft', // New Field
            'can_redeem' => 'required|boolean',         // New Field
        ]);

        DB::beginTransaction();
        try {
            // 2. Transform Logic
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

    // --- STEP 3: SETTINGS ---


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

        $booleanKeys = [
            'auto_duration',
            'auto_grading',
            'enable_negative_marking',
            'enable_section_cutoff',
            'shuffle_questions',
            'restrict_attempts',
            'disable_section_navigation',
            'disable_finish_button',
            'disable_question_navigation',
            'list_questions',
            'hide_solutions',
            'show_leaderboard'
        ];

        $valueKeys = ['cutoff', 'no_of_attempts', 'duration_mode', 'marks_mode'];

        $newSettings = [];

        foreach ($booleanKeys as $key) {
            $newSettings[$key] = $request->has($key) ? true : false;
        }
        foreach ($valueKeys as $key) {
            $newSettings[$key] = $request->input($key);
        }

        DB::beginTransaction();
        try {
            $exam->settings = $newSettings;
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
            return redirect()->route('admin.exams.sections.index', ['exam' => $exam->id])
                ->with('success', 'Exam Settings Updated.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Failed to save settings.');
        }
    }

    // --- STEP 4: DELETE ---

    public function destroy($id)
    {
        try {
            $exam = Exam::findOrFail($id);

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
            return redirect()->route('admin.exams.index')
                ->with('error', 'Unable to Delete Exam. Remove all associations and try again!');
        }

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam was successfully deleted!');
    }
}
