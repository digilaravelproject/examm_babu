<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Http\Requests\Admin\UpdateExamSettingsRequest;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\SubCategory;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ExamController extends Controller
{
    private ExamRepository $repository;

    public function __construct(ExamRepository $repository)
    {
        // Standard Middleware
        // $this->middleware(['role:admin|instructor'])->except(['search', 'index']);
        $this->repository = $repository;
    }

    /**
     * List all exams with search/filters
     */
    public function index(Request $request)
    {
        $query = Exam::with(['subCategory:id,name', 'examType:id,name'])
                    ->withCount('examSections');

        // Filtering Logic for Blade
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('exam_type_id', $request->type);
        }

        $exams = $query->latest('id')->paginate($request->get('perPage', 10))->withQueryString();
        $examTypes = ExamType::active()->get(['id', 'name']);

        if ($request->ajax()) {
            return view('admin.exams.partials.table', compact('exams'))->render();
        }

        return view('admin.exams.index', compact('exams', 'examTypes'));
    }

    /**
     * Create View
     */
    public function create()
    {
        $exam = new Exam(); // Empty model for _form.blade
        $examTypes = ExamType::active()->get();
        $subCategories = SubCategory::active()->get();
        $steps = $this->repository->getSteps();

        return view('admin.exams.create', compact('exam', 'examTypes', 'subCategories', 'steps'));
    }

    /**
     * Store Exam
     */
    public function store(StoreExamRequest $request)
    {
        DB::beginTransaction();
        try {
            $exam = Exam::create($request->validated());

            DB::commit();
            return redirect()->route('admin.exams.settings', $exam->id)
                             ->with('success', 'Exam created successfully! Now configure settings.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Exam Store Error: " . $e->getMessage());
            return back()->with('error', 'Failed to create exam.')->withInput();
        }
    }

    /**
     * Edit View (Details Step)
     */
    public function edit($id)
    {
        $exam = Exam::findOrFail($id);
        $examTypes = ExamType::active()->get();
        $subCategories = SubCategory::active()->get();
        $steps = $this->repository->getSteps($exam->id, 'details');

        return view('admin.exams.edit', compact('exam', 'examTypes', 'subCategories', 'steps'));
    }

    /**
     * Update Exam (Details)
     */
    public function update(UpdateExamRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $exam = Exam::findOrFail($id);
            $exam->update($request->validated());

            DB::commit();
            return redirect()->route('admin.exams.settings', $exam->id)
                             ->with('success', 'Exam details updated!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Exam Update Error: " . $e->getMessage());
            return back()->with('error', 'Update failed!')->withInput();
        }
    }

    /**
     * Settings View
     */
    public function settings($id)
    {
        $exam = Exam::findOrFail($id);
        $steps = $this->repository->getSteps($exam->id, 'settings');

        // Defaults mapping from your old Inertia logic
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
        ];

        return view('admin.exams.settings', compact('exam', 'steps', 'settings'));
    }

    /**
     * Update Settings
     */
    public function updateSettings(UpdateExamSettingsRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $exam = Exam::with('examSections')->findOrFail($id);

            // Boolean handling for Blade checkboxes
            $keys = [
                'auto_duration', 'auto_grading', 'enable_section_cutoff',
                'enable_negative_marking', 'restrict_attempts', 'disable_section_navigation',
                'disable_question_navigation', 'disable_finish_button', 'hide_solutions',
                'list_questions', 'shuffle_questions', 'show_leaderboard'
            ];

            $data = $request->validated();
            foreach ($keys as $key) {
                $data[$key] = $request->boolean($key);
            }

            $exam->settings = $data;
            $exam->save();

            // Refresh Meta logic from your old version
            foreach ($exam->examSections as $section) {
                $section->updateMeta();
            }
            $exam->updateMeta();

            DB::commit();
            return redirect()->route('admin.exams.sections.index', $exam->id)
                             ->with('success', 'Settings updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Settings Update Error: " . $e->getMessage());
            return back()->with('error', 'Failed to update settings.');
        }
    }

    /**
     * Secure Delete
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $exam = Exam::findOrFail($id);

            // Force deleting as per your old logic
            $exam->examSchedules()->forceDelete();
            $exam->sessions()->forceDelete();
            $exam->questions()->detach();
            $exam->examSections()->forceDelete();

            // If you have secureDelete trait
            if(method_exists($exam, 'secureDelete')) {
                $exam->secureDelete('examSections', 'sessions', 'examSchedules');
            } else {
                $exam->delete();
            }

            DB::commit();
            return redirect()->route('admin.exams.index')->with('success', 'Exam deleted!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Unable to Delete. Remove associations first.');
        }
    }

    /**
     * API Search
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        $exams = Exam::select(['id', 'title'])
                    ->where('title', 'like', "%{$query}%")
                    ->published()->limit(20)->get();

        return response()->json(['exams' => $exams]);
    }
}
