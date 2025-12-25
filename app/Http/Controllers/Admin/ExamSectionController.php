<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamSectionRequest; // Ensure you use this if you created the file
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
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all exam sections
     */
    public function index($examId)
    {
        $exam = Exam::with(['examSections' => function($query) {
            $query->with('section:id,name')->orderBy('section_order');
        }])->findOrFail($examId);

        $availableSections = Section::where('is_active', 1)->select('id', 'name')->get();
        $steps = $this->repository->getSteps($exam->id, 'sections');

        return view('admin.exams.sections.index', compact('exam', 'availableSections', 'steps'));
    }

    /**
     * Store an exam section
     */
    public function store(Request $request, Exam $exam)
    {
        // 1. Validate Request
        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'section_order' => 'required|integer',
            'correct_marks' => 'required|numeric|min:0',
            'negative_marks' => 'nullable|numeric|min:0',
            'negative_marking_type' => 'nullable|in:fixed,percentage',
            'section_cutoff' => 'nullable|numeric|min:0|max:100',
            'total_duration' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $examSection = new ExamSection();
            $examSection->exam_id = $exam->id;
            $examSection->section_id = $request->section_id;
            $examSection->name = $request->name;
            $examSection->section_order = $request->section_order;

            // Scoring Fields
            $examSection->correct_marks = $request->correct_marks;
            $examSection->negative_marking_type = $request->negative_marking_type ?? 'fixed';
            $examSection->negative_marks = $request->negative_marks ?? 0;
            $examSection->section_cutoff = $request->section_cutoff ?? 0;

            // --- CRITICAL LOGIC FIX ---
            // We check the EXAM SETTINGS (Database), not the Request

            // 1. Duration Logic
            $autoDuration = $exam->settings['auto_duration'] ?? true;

            if ($autoDuration) {
                // New section has 0 questions, so duration is 0
                $examSection->total_duration = 0;
            } else {
                // Manual mode: use input duration (minutes to seconds)
                $examSection->total_duration = ($request->total_duration ?? 0) * 60;
            }

            // 2. Grading Logic
            $autoGrading = $exam->settings['auto_grading'] ?? true;

            if ($autoGrading) {
                // New section has 0 questions, so total marks is 0
                $examSection->total_marks = 0;
            } else {
                // Manual mode: 0 questions * correct marks = 0
                $examSection->total_marks = 0;
            }

            $examSection->save();

            // Update Parent Exam Meta (Total Duration/Marks of the whole exam)
            $exam->updateMeta();

            DB::commit();
            // return redirect()->back()->with('success', 'Exam Section successfully added!');
            return redirect()->route('admin.exams.questions.index', $exam->id)
                ->with('success', 'Exam Section added! Now add questions.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Error adding section: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Edit API Endpoint
     */
    public function edit(Exam $exam, $id)
    {
        $section = ExamSection::with(['section:id,name'])->findOrFail($id);

        // Convert seconds to minutes for display in form
        $section->total_duration_minutes = $section->total_duration > 0 ? $section->total_duration / 60 : 0;

        return response()->json($section);
    }

    /**
     * Update a section
     */
    public function update(Request $request, Exam $exam, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'section_order' => 'required|integer',
            'correct_marks' => 'required|numeric|min:0',
            'negative_marks' => 'nullable|numeric|min:0',
            'negative_marking_type' => 'nullable|in:fixed,percentage',
            'section_cutoff' => 'nullable|numeric',
            'total_duration' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $examSection = ExamSection::findOrFail($id);

            $examSection->name = $request->name;
            $examSection->section_id = $request->section_id;
            $examSection->section_order = $request->section_order;
            $examSection->correct_marks = $request->correct_marks;
            $examSection->negative_marking_type = $request->negative_marking_type ?? 'fixed';
            $examSection->negative_marks = $request->negative_marks ?? 0;
            $examSection->section_cutoff = $request->section_cutoff ?? 0;

            // --- CRITICAL LOGIC FIX ---

            $autoDuration = $exam->settings['auto_duration'] ?? true;
            $autoGrading = $exam->settings['auto_grading'] ?? true;

            // 1. Duration Logic (Update)
            if ($autoDuration) {
                // Recalculate based on existing questions in DB
                $examSection->total_duration = $examSection->questions()->sum('default_time');
            } else {
                // Use Manual Input
                $examSection->total_duration = ($request->total_duration ?? 0) * 60;
            }

            // 2. Grading Logic (Update)
            if ($autoGrading) {
                // Recalculate based on existing questions in DB
                $examSection->total_marks = $examSection->questions()->sum('default_marks');
            } else {
                // Manual Calculation: Count * Correct Marks
                $examSection->total_marks = $examSection->questions()->count() * $request->correct_marks;
            }

            $examSection->save();

            // Recalculate everything
            if(method_exists($examSection, 'updateMeta')) {
                $examSection->updateMeta();
            }
            $exam->updateMeta();

            DB::commit();
            // return redirect()->back()->with('success', 'Exam Section successfully updated!');
            return redirect()->route('admin.exams.questions.index', $exam->id)
                ->with('success', 'Exam Section successfully updated!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a section
     */
    public function destroy(Exam $exam, $id)
    {
        try {
            $examSection = ExamSection::withCount(['examSessions'])->findOrFail($id);

            // Check associations
            if(method_exists($examSection, 'canSecureDelete') && !$examSection->canSecureDelete('examSessions')) {
                 $count = $examSection->exam_sessions_count;
                 return redirect()->back()->with('error', "Cannot delete section. It is associated with $count exam sessions.");
            }

            DB::transaction(function () use ($examSection) {
                // Detach questions
                if(method_exists($examSection, 'questions')) {
                    $examSection->questions()->detach();
                }

                // Delete
                if(method_exists($examSection, 'secureDelete')) {
                    $examSection->secureDelete('examSessions');
                } else {
                    $examSection->delete();
                }
            });

            $exam->updateMeta();
            return redirect()->back()->with('success', 'Section successfully deleted!');

        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Database Error: Unable to delete section. It might be in use.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting section.');
        }
    }
}
