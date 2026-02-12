<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Section;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ExamSectionController_old extends Controller
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
        // Eager load sections properly
        $exam = Exam::with(['examSections' => function($query) {
            $query->with('section:id,name')
                  ->withCount('questions') // Ensure question count is loaded
                  ->orderBy('section_order');
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
        // 1. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'section_order' => 'required|integer',
            'correct_marks' => 'required|numeric|min:0',
            'negative_marks' => 'nullable|numeric|min:0',
            'negative_marking_type' => 'nullable|in:fixed,percentage',
            'section_cutoff' => 'nullable|numeric|min:0|max:100',
            // Duration is required if auto_duration is false, but we keep it nullable for safety
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

            // --- DURATION LOGIC ---
            $autoDuration = $exam->settings['auto_duration'] ?? true;

            if ($autoDuration) {
                // Auto: Duration is sum of questions (0 initially)
                $examSection->total_duration = 0;
            } else {
                // Manual: User Input (Minutes -> Seconds)
                $examSection->total_duration = ($request->total_duration ?? 0) * 60;
            }

            // --- GRADING LOGIC ---
            $autoGrading = $exam->settings['auto_grading'] ?? true;

            if ($autoGrading) {
                // Auto: Marks sum of questions (0 initially)
                $examSection->total_marks = 0;
            } else {
                // Manual: Marks = Questions Count * Correct Marks (0 initially)
                $examSection->total_marks = 0;
            }

            $examSection->save();

            // Update Parent Exam Meta
            $exam->updateMeta();

            DB::commit();
            return redirect()->route('admin.exams.questions.index', $exam->id)
                ->with('success', 'Exam Section added successfully!');

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

        // Calculate minutes for the form input
        // If duration is 3600s, return 60 mins
        $section->duration_minutes = $section->total_duration > 0 ? floor($section->total_duration / 60) : 0;

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
            'total_duration' => 'nullable|integer|min:0',
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

            $autoDuration = $exam->settings['auto_duration'] ?? true;
            $autoGrading = $exam->settings['auto_grading'] ?? true;

            // 1. Duration Logic (Update)
            if ($autoDuration) {
                // Recalculate based on existing questions
                $examSection->total_duration = $examSection->questions()->sum('default_time');
            } else {
                // Use Manual Input (Minutes -> Seconds)
                $examSection->total_duration = ($request->total_duration ?? 0) * 60;
            }

            // 2. Grading Logic (Update)
            if ($autoGrading) {
                $examSection->total_marks = $examSection->questions()->sum('default_marks');
            } else {
                // Manual Calculation: Count * Correct Marks
                $examSection->total_marks = $examSection->questions()->count() * $request->correct_marks;
            }

            $examSection->save();

            // Recalculate Exam Meta
            if(method_exists($examSection, 'updateMeta')) {
                $examSection->updateMeta();
            }
            $exam->updateMeta();

            DB::commit();
            return redirect()->back()->with('success', 'Exam Section updated successfully!');

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

            DB::transaction(function () use ($examSection, $exam) {
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

                // Update Exam Meta after delete
                $exam->updateMeta();
            });

            return redirect()->back()->with('success', 'Section successfully deleted!');

        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Database Error: Unable to delete section. It might be in use.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting section.');
        }
    }
}
