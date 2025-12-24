<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamSection;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request, Exam $exam)
    {
        $steps = $this->repository->getSteps($exam->id, 'questions');
        $examSections = $exam->examSections;

        // Active Section tab
        $activeSectionId = $request->get('section_id', $examSections->first()->id ?? null);
        $activeSection = $examSections->where('id', $activeSectionId)->first();

        // Get Questions already in this section
        $addedQuestions = $activeSection ? $activeSection->questions : collect([]);

        return view('admin.exams.questions.index', compact('exam', 'steps', 'examSections', 'activeSection', 'addedQuestions'));
    }

    // This method handles the "Add Questions" popup/modal logic
    public function store(Request $request, Exam $exam)
    {
        $request->validate([
            'section_id' => 'required|exists:exam_sections,id',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        $section = ExamSection::find($request->section_id);

        // Attach to Pivot (exam_questions) if you use a direct relation
        // Or attach to section_questions pivot
        // Assuming relationship: ExamSection belongsToMany Questions
        $section->questions()->syncWithoutDetaching($request->question_ids);

        // IMPORTANT: Also link to the main Exam pivot if your architecture requires it
        $exam->questions()->syncWithoutDetaching($request->question_ids);

        // Update Totals
        $section->updateMeta();
        $exam->updateMeta();

        return back()->with('success', 'Questions added successfully');
    }

    public function destroy(Exam $exam, $questionId)
    {
         // Logic to detach question
         // Find which section this question belongs to in this exam and detach
         return back()->with('success', 'Question removed');
    }
}
