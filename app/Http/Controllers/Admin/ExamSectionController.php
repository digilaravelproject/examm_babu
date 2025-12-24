<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Section; // The Master Section Table
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ExamSectionController extends Controller
{
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Exam $exam)
    {
        $exam->load('examSections.section'); // Eager load
        $availableSections = Section::where('is_active', 1)->get();
        $steps = $this->repository->getSteps($exam->id, 'sections');

        return view('admin.exams.sections.index', compact('exam', 'availableSections', 'steps'));
    }

    public function store(Request $request, Exam $exam)
    {
        $request->validate([
            'display_name' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'total_duration' => 'nullable|integer',
            'correct_marks' => 'required|numeric',
            'negative_marks' => 'nullable|numeric',
            'section_order' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            $section = new ExamSection();
            $section->exam_id = $exam->id;
            $section->section_id = $request->section_id;
            $section->display_name = $request->display_name;
            $section->section_order = $request->section_order;

            // Handle Settings Logic from Step 2
            $autoDuration = $exam->settings['auto_duration'] ?? true;
            $autoGrading = $exam->settings['auto_grading'] ?? true;

            // Duration
            if ($autoDuration) {
                $section->total_duration = 0; // Will update when questions are added
            } else {
                $section->total_duration = ($request->total_duration ?? 0) * 60; // Store in seconds
            }

            // Marks
            if ($autoGrading) {
                $section->total_marks = 0; // Will update later
            } else {
                // If manual, we need questions count, currently 0
                $section->total_marks = 0;
            }

            // Store meta for manual calculation later
            $section->metadata = [
                'correct_marks' => $request->correct_marks,
                'negative_marks' => $request->negative_marks ?? 0
            ];

            $section->save();
            $exam->updateMeta(); // Update Exam totals

            DB::commit();
            return back()->with('success', 'Section Added.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Exam $exam, ExamSection $section)
    {
        // ... Validation similar to store ...
        $section->update([
            'display_name' => $request->display_name,
            'section_order' => $request->section_order,
            'total_duration' => ($request->total_duration ?? 0) * 60,
             // Update metadata for marks
            'metadata->correct_marks' => $request->correct_marks,
            'metadata->negative_marks' => $request->negative_marks
        ]);

        $section->updateMeta(); // Recalculate based on existing questions
        $exam->updateMeta();

        return back()->with('success', 'Section Updated');
    }

    public function destroy(Exam $exam, ExamSection $section)
    {
        $section->questions()->detach();
        $section->delete();
        $exam->updateMeta();
        return back()->with('success', 'Section Deleted');
    }
}
