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
        $this->repository = $repository;
    }

    // --- STEP 1: DETAILS ---

    public function index(Request $request)
    {
        $exams = Exam::with(['subCategory', 'examType'])->latest()->paginate(10);
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        $exam = new Exam();
        $examTypes = ExamType::where('is_active', 1)->get();
        $subCategories = SubCategory::where('is_active', 1)->get();
        // Assuming getSteps returns the array for the wizard header
        $steps = $this->repository->getSteps(null, 'details');

        return view('admin.exams.create', compact('exam', 'examTypes', 'subCategories', 'steps'));
    }

    public function store(Request $request)
    {
        // Validation directly here for simplicity, or use StoreExamRequest
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid', // Form field: pricing_type
            'visibility' => 'required|in:public,private', // Form field: visibility
        ]);

        DB::beginTransaction();
        try {
            // Convert Dropdown logic to Database Booleans
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';
            $data['code'] = 'EX-' . strtoupper(Str::random(8));

            // Remove non-column fields
            unset($data['pricing_type'], $data['visibility']);

            $exam = Exam::create($data);

            DB::commit();
            return redirect()->route('admin.exams.settings', $exam->id)
                ->with('success', 'Exam Details Saved. Proceed to Settings.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Error creating exam.')->withInput();
        }
    }

    public function edit(Exam $exam)
    {
        $examTypes = ExamType::where('is_active', 1)->get();
        $subCategories = SubCategory::where('is_active', 1)->get();
        $steps = $this->repository->getSteps($exam->id, 'details');

        return view('admin.exams.edit', compact('exam', 'examTypes', 'subCategories', 'steps'));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:free,paid',
            'visibility' => 'required|in:public,private',
        ]);

        DB::beginTransaction();
        try {
            $data['is_paid'] = $request->pricing_type === 'paid';
            $data['is_private'] = $request->visibility === 'private';
            unset($data['pricing_type'], $data['visibility']);

            $exam->update($data);

            DB::commit();
            return redirect()->route('admin.exams.settings', $exam->id)
                ->with('success', 'Details Updated. Proceed to Settings.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    // --- STEP 2: SETTINGS ---

    public function settings(Exam $exam)
    {
        $steps = $this->repository->getSteps($exam->id, 'settings');

        // Ensure default settings exist if null
        $currentSettings = $exam->settings ?? [];

        return view('admin.exams.settings', compact('exam', 'steps', 'currentSettings'));
    }

    public function updateSettings(Request $request, Exam $exam)
    {
        // 1. Define all possible boolean keys based on your list
        $booleanKeys = [
            'auto_duration', 'auto_grading', 'enable_negative_marking',
            'enable_section_cutoff', 'shuffle_questions', 'restrict_attempts',
            'disable_section_navigation', 'disable_finish_button',
            'list_questions', 'hide_solutions', 'show_leaderboard'
        ];

        // 2. Define value keys
        $valueKeys = ['cutoff', 'no_of_attempts', 'duration_mode', 'marks_mode'];

        $newSettings = [];

        // 3. Process Booleans (Checkbox logic: if present = true, else false)
        foreach ($booleanKeys as $key) {
            $newSettings[$key] = $request->has($key) ? true : false;
        }

        // 4. Process Values
        foreach ($valueKeys as $key) {
            $newSettings[$key] = $request->input($key);
        }

        DB::beginTransaction();
        try {
            $exam->settings = $newSettings;
            $exam->save();

            // Recalculate meta in case auto_grading changed
            $exam->updateMeta();

            DB::commit();
            return redirect()->route('admin.exams.sections.index', $exam->id)
                ->with('success', 'Settings Saved. Proceed to Sections.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save settings.');
        }
    }
}
