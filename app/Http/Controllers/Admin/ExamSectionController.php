<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamSectionRequest;
use App\Http\Requests\Admin\UpdateExamSectionRequest;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Section;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ExamSectionController extends Controller
{
    private ExamRepository $repository;

    public function __construct(ExamRepository $repository) {
        $this->repository = $repository;
    }

    public function index(Exam $exam) {
        $exam->load(['examSections' => function($query) {
            $query->with('section:id,name')->orderBy('section_order');
        }]);

        $availableSections = Section::active()->get(['id', 'name']);
        $steps = $this->repository->getSteps($exam->id, 'sections');

        return view('admin.exams.sections.index', compact('exam', 'availableSections', 'steps'));
    }

    public function store(StoreExamSectionRequest $request, Exam $exam) {
        DB::beginTransaction();
        try {
            $section = new ExamSection($request->validated());
            $section->exam_id = $exam->id;

            // Logic for Duration & Marks (Same for Store & Update)
            $this->calculateSectionMetrics($section, $exam, $request);

            $section->save();
            $exam->updateMeta();

            DB::commit();
            return back()->with('success', 'Exam Section added successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("ExamSection Store Error: " . $e->getMessage());
            return back()->with('error', 'Store Failed!')->withInput();
        }
    }

    public function edit(Exam $exam, ExamSection $section) {
        // Blade context mein aksar hum modal use karte hain,
        // toh ye JSON return karega AJAX request ke liye
        return response()->json($section->load('section:id,name'));
    }

    public function update(UpdateExamSectionRequest $request, Exam $exam, ExamSection $section) {
        DB::beginTransaction();
        try {
            $section->fill($request->validated());

            $this->calculateSectionMetrics($section, $exam, $request);

            $section->save();
            $section->updateMeta(); // If model has this method
            $exam->updateMeta();

            DB::commit();
            return back()->with('success', 'Exam Section updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("ExamSection Update Error: " . $e->getMessage());
            return back()->with('error', 'Update Failed!');
        }
    }

    public function destroy(Exam $exam, ExamSection $section) {
        DB::beginTransaction();
        try {
            // Association Check (Using your old logic)
            if (method_exists($section, 'canSecureDelete') && !$section->canSecureDelete('examSessions')) {
                return back()->with('error', 'Cannot delete: Section has active student sessions.');
            }

            $section->questions()->detach();
            $section->delete();

            $exam->updateMeta();

            DB::commit();
            return back()->with('success', 'Section removed from exam.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Delete Failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper to calculate duration and marks based on exam settings
     */
    private function calculateSectionMetrics_old($section, $exam, $request) {
        // Duration Calculation
        if ($exam->settings->get('auto_duration', true)) {
            $section->total_duration = $section->questions()->sum('default_time') ?? 0;
        } else {
            $section->total_duration = ($request->total_duration ?? 0) * 60;
        }

        // Grading Calculation
        if ($exam->settings->get('auto_grading', true)) {
            $section->total_marks = $section->questions()->sum('default_marks') ?? 0;
        } else {
            $section->total_marks = ($section->questions()->count() ?? 0) * ($request->correct_marks ?? 0);
        }
    }
    private function calculateSectionMetrics(ExamSection $section, Exam $exam, $request): void
    {
        // Auto Duration Logic
        if ($request->boolean('auto_duration')) {
            $section->total_duration = $section->questions()->sum('default_time') ?? 0;
        } else {
            $section->total_duration = ($request->integer('total_duration') ?? 0) * 60;
        }

        // Auto Grading Logic
        if ($request->boolean('auto_grading')) {
            $section->total_marks = $section->questions()->sum('default_marks') ?? 0;
        } else {
            $count = $section->questions()->count();
            $section->total_marks = $count * ($request->float('correct_marks') ?? 0);
        }
    }
}
