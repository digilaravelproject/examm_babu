<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\DifficultyLevel;
use App\Models\Topic;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ExamQuestionController extends Controller
{
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Page: Manage Questions for an Exam
     */
    public function index($examId)
    {
        $exam = Exam::withCount('examSections')->findOrFail($examId);

        if($exam->exam_sections_count == 0) {
            return redirect()->route('admin.exams.sections.index', $exam->id)
                ->with('error', 'Please add at least one section before adding questions.');
        }

        // Load necessary data for filters
        $examSections = $exam->examSections()->orderBy('section_order')->get();
        $questionTypes = QuestionType::where('is_active', 1)->get();
        $difficultyLevels = DifficultyLevel::where('is_active', 1)->get();
        $topics = Topic::where('is_active', 1)->select('id', 'name')->limit(100)->get(); // Limit for performance

        $steps = $this->repository->getSteps($exam->id, 'questions');

        return view('admin.exams.questions.index', compact(
            'exam',
            'examSections',
            'questionTypes',
            'difficultyLevels',
            'topics',
            'steps'
        ));
    }

    /**
     * AJAX: Fetch questions attached to a specific Exam Section
     */
    public function fetchExamQuestions(Request $request, $examId, $sectionId)
    {
        $examSection = ExamSection::where('exam_id', $examId)->findOrFail($sectionId);

        // We use DB table join to avoid Model relation timestamp issues
        $query = DB::table('exam_questions')
            ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
            ->leftJoin('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->leftJoin('difficulty_levels', 'questions.difficulty_level_id', '=', 'difficulty_levels.id')
            ->where('exam_questions.exam_section_id', $sectionId)
            ->select(
                'questions.id',
                'questions.question',
                'questions.default_marks',
                'question_types.name as type',
                'question_types.code as type_code',
                'difficulty_levels.name as difficulty'
            );

        if ($request->search) {
            $query->where('questions.question', 'like', '%' . $request->search . '%');
        }

        $questions = $query->paginate(10);

        return response()->json($questions);
    }

    /**
     * AJAX: Fetch Available Questions (Global Bank)
     */
    public function fetchAvailableQuestions(Request $request, $examId, $sectionId)
{
    // 1. Get IDs of questions already added to this exam section (to exclude them)
    $existingIds = DB::table('exam_questions')
        ->where('exam_section_id', $sectionId)
        ->pluck('question_id')
        ->toArray();

    // 2. Start Query on Global Question Bank (Exclude only added ones)
    $query = Question::with(['questionType:id,name,code', 'difficultyLevel:id,name', 'topic:id,name'])
        ->whereNotIn('id', $existingIds);

    // --- APPLY FILTERS ONLY IF SELECTED ---

    // Search (Code OR Question Text - Multilanguage compatible)
    if ($request->filled('search')) {
        $term = $request->search;
        $query->where(function($q) use ($term) {
            $q->where('code', 'like', '%' . $term . '%')
              ->orWhere('question', 'like', '%' . $term . '%'); // Searches HTML text too
        });
    }

    // Filter by Type
    if ($request->filled('type')) {
        $query->where('question_type_id', $request->type);
    }

    // Filter by Difficulty
    if ($request->filled('difficulty')) {
        $query->where('difficulty_level_id', $request->difficulty);
    }

    // Filter by Topic
    if ($request->filled('topic')) {
        $query->where('topic_id', $request->topic);
    }

    // Filter by Skill (if you have it)
    if ($request->filled('skill')) {
        $query->where('skill_id', $request->skill);
    }

    // 3. Get Results (Latest first)
    $questions = $query->orderBy('id', 'desc')->paginate(10);

    return response()->json($questions);
}
    public function fetchAvailableQuestions_old(Request $request, $examId, $sectionId)
    {
        $examSection = ExamSection::findOrFail($sectionId);

        // Get IDs of questions already in this section to exclude them
        $existingIds = DB::table('exam_questions')
            ->where('exam_section_id', $sectionId)
            ->pluck('question_id')
            ->toArray();

        // Query Global Question Bank
        $query = Question::with(['questionType', 'difficultyLevel'])
            ->whereNotIn('id', $existingIds)
            ->where('section_id', $examSection->section_id); // Match Master Section Type

        // Filters
        if ($request->search) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }
        if ($request->type) {
            $query->where('question_type_id', $request->type);
        }
        if ($request->difficulty) {
            $query->where('difficulty_level_id', $request->difficulty);
        }
        if ($request->topic) {
            $query->where('topic_id', $request->topic);
        }

        $questions = $query->paginate(10);

        return response()->json($questions);
    }

    /**
     * AJAX: Add Question to Section
     */
    public function addQuestion(Request $request, $examId, $sectionId)
    {
        try {
            $questionId = $request->question_id;

            // Direct DB Insert to bypass Model Timestamp issues
            $exists = DB::table('exam_questions')
                ->where('exam_section_id', $sectionId)
                ->where('question_id', $questionId)
                ->exists();

            if (!$exists) {
                DB::table('exam_questions')->insert([
                    'exam_id' => $examId,
                    'exam_section_id' => $sectionId,
                    'question_id' => $questionId
                    // No created_at/updated_at
                ]);

                // Update Meta Logic
                $section = ExamSection::find($sectionId);
                $exam = Exam::find($examId);

                if(method_exists($section, 'updateMeta')) $section->updateMeta();
                if(method_exists($exam, 'updateMeta')) $exam->updateMeta();

                return response()->json(['status' => 'success', 'message' => 'Question added.']);
            }

            return response()->json(['status' => 'warning', 'message' => 'Question already exists in section.']);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Remove Question from Section
     */
    public function removeQuestion(Request $request, $examId, $sectionId)
    {
        try {
            $questionId = $request->question_id;

            // Direct DB Delete
            DB::table('exam_questions')
                ->where('exam_section_id', $sectionId)
                ->where('question_id', $questionId)
                ->delete();

            // Update Meta Logic
            $section = ExamSection::find($sectionId);
            $exam = Exam::find($examId);

            if(method_exists($section, 'updateMeta')) $section->updateMeta();
            if(method_exists($exam, 'updateMeta')) $exam->updateMeta();

            return response()->json(['status' => 'success', 'message' => 'Question removed.']);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
