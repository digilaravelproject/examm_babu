<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DifficultyLevel;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Topic;
use App\Repositories\ExamRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamQuestionController extends Controller
{
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index($examId)
    {
        $exam = Exam::withCount('examSections')->findOrFail($examId);

        if ($exam->exam_sections_count == 0) {
            return redirect()->route('admin.exams.sections.index', $exam->id)
                ->with('error', 'Please add at least one section before adding questions.');
        }

        $examSections = $exam->examSections()->orderBy('section_order')->get();
        $questionTypes = QuestionType::where('is_active', 1)->get();
        $difficultyLevels = DifficultyLevel::where('is_active', 1)->get();
        $topics = Topic::where('is_active', 1)->select('id', 'name')->limit(100)->get();

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
        $perPage = $request->input('per_page', 10);

        $query = DB::table('exam_questions')
            ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
            ->leftJoin('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->leftJoin('difficulty_levels', 'questions.difficulty_level_id', '=', 'difficulty_levels.id')
            ->where('exam_questions.exam_section_id', $sectionId)
            ->select(
                'questions.id',
                'questions.question',
                'questions.default_marks',
                'question_types.code as type_code',
                'difficulty_levels.name as difficulty'
            );

        if ($request->search) {
            $query->where('questions.question', 'like', '%' . $request->search . '%');
        }

        $questions = $query->paginate($perPage);
        return response()->json($questions);
    }

    /**
     * AJAX: Fetch ALL Question IDs already in the entire exam (To prevent 1062 error)
     */
    public function fetchAllExamQuestionIds($examId)
    {
        $ids = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->pluck('question_id');

        return response()->json($ids);
    }

    /**
     * AJAX: Fetch Available Questions (Global Bank)
     */
    public function fetchAvailableQuestions(Request $request, $examId, $sectionId)
    {
        $perPage = $request->input('per_page', 10);

        // Crucial: Exclude questions already in ANY section of this exam
        $existingIdsInExam = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->pluck('question_id')
            ->toArray();

        $query = Question::with(['questionType:id,name,code', 'difficultyLevel:id,name', 'topic:id,name'])
            ->whereNotIn('id', $existingIdsInExam);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', '%' . $term . '%')
                    ->orWhere('question', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('type')) $query->where('question_type_id', $request->type);
        if ($request->filled('difficulty')) $query->where('difficulty_level_id', $request->difficulty);
        if ($request->filled('topic')) $query->where('topic_id', $request->topic);

        $questions = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($questions);
    }

    public function addQuestion(Request $request, $examId, $sectionId)
    {
        try {
            $questionId = $request->question_id;

            // Global exist check for 1062 prevention
            $existsGlobal = DB::table('exam_questions')
                ->where('exam_id', $examId)
                ->where('question_id', $questionId)
                ->exists();

            if (!$existsGlobal) {
                DB::table('exam_questions')->insert([
                    'exam_id' => $examId,
                    'exam_section_id' => $sectionId,
                    'question_id' => $questionId
                ]);

                $section = ExamSection::find($sectionId);
                $exam = Exam::find($examId);
                if (method_exists($section, 'updateMeta')) $section->updateMeta();
                if (method_exists($exam, 'updateMeta')) $exam->updateMeta();

                return response()->json(['status' => 'success', 'message' => 'Question added.']);
            }

            return response()->json(['status' => 'error', 'message' => 'Question already used in this exam.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function removeQuestion(Request $request, $examId, $sectionId)
    {
        try {
            $questionId = $request->question_id;
            DB::table('exam_questions')
                ->where('exam_section_id', $sectionId)
                ->where('question_id', $questionId)
                ->delete();

            $section = ExamSection::find($sectionId);
            $exam = Exam::find($examId);
            if (method_exists($section, 'updateMeta')) $section->updateMeta();
            if (method_exists($exam, 'updateMeta')) $exam->updateMeta();

            return response()->json(['status' => 'success', 'message' => 'Question removed.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
