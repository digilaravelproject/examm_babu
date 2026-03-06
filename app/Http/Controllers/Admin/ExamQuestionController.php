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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExamQuestionController extends Controller
{
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    // --- HELPER METHODS ---

    private function getRoutePrefix(): string
    {
        return Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
    }

    private function getRouteParams(): array
    {
        if (Auth::user()->hasRole('admin')) {
            return [];
        }
        return ['role' => request()->route('role') ?? 'instructor'];
    }

    private function authorizeInstructor(Exam $exam): void
    {
        // 1. If Admin or has global management permission, allow immediately
        if (Auth::user()->hasRole('admin') || Auth::user()->can('manage exams')) {
            return;
        }

        // 2. If Exam has no creator, assume Admin owned it -> Block Instructor
        if (is_null($exam->created_by)) {
            abort(403, 'System protected exam. You cannot modify it.');
        }

        // 3. Strict Ownership Check for Instructors
        if ($exam->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this exam.');
        }
    }

    // --- MAIN METHODS ---

    public function index(Request $request)
    {
        // FIX: Retrieve parameter by Name, not argument
        $examId = $request->route('exam');
        $exam = Exam::withCount('examSections')->findOrFail($examId);

        $this->authorizeInstructor($exam);

        if ($exam->exam_sections_count == 0) {
            // FIX: Pass params correctly for redirect
            $params = array_merge($this->getRouteParams(), ['exam' => $exam->id]);
            return redirect()->route($this->getRoutePrefix() . 'exams.sections.index', $params)
                ->with('error', 'Please add at least one section before adding questions.');
        }

        $examSections = $exam->examSections()->orderBy('section_order')->get();
        $questionTypes = QuestionType::where('is_active', 1)->get();
        $difficultyLevels = DifficultyLevel::where('is_active', 1)->get();

        $topics = Topic::active()
            ->with(['skill.microCategory.subCategory'])
            ->limit(500)
            ->get()
            ->map(function ($topic) {
                $subject = $topic->skill->name ?? 'No Subject';
                $micro = $topic->skill->microCategory->name ?? 'No Micro';
                $sub = $topic->skill->microCategory->subCategory->name ?? 'No SubCategory';

                return [
                    'id' => $topic->id,
                    'skill_id' => $topic->skill_id,
                    'name' => "{$topic->name} ({$subject} | {$micro} | {$sub})"
                ];
            });

        $skills = \App\Models\Skill::active()->select('id', 'name')->orderBy('name')->get();

        $steps = $this->repository->getSteps($exam->id, 'questions');

        return view('admin.exams.questions.index', compact(
            'exam', 'examSections', 'questionTypes', 'difficultyLevels', 'topics', 'skills', 'steps'
        ));
    }

    public function fetchExamQuestions(Request $request)
    {
        // FIX: Retrieve parameter by Name
        $examId = $request->route('exam');
        $sectionId = $request->route('section'); // Passed via URL /{exam}/sections/{section}/questions

        $exam = Exam::findOrFail($examId);
        $this->authorizeInstructor($exam);

        $perPage = $request->input('per_page', 10);

        $query = DB::table('exam_questions')
            ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
            ->leftJoin('question_types', 'questions.question_type_id', '=', 'question_types.id')
            ->leftJoin('difficulty_levels', 'questions.difficulty_level_id', '=', 'difficulty_levels.id')
            ->leftJoin('topics', 'questions.topic_id', '=', 'topics.id')
            ->where('exam_questions.exam_section_id', $sectionId)
            ->select(
                'questions.id',
                'questions.question',
                'questions.default_marks',
                'questions.has_attachment',
                'questions.attachment_type',
                'question_types.code as type_code',
                'difficulty_levels.name as difficulty',
                'topics.name as topic_name'
            )
            ->whereNull('questions.deleted_at');

        if ($request->search) {
            $query->where('questions.question', 'like', '%' . $request->search . '%');
        }

        $questions = $query->paginate($perPage);
        return response()->json($questions);
    }

    public function fetchAllExamQuestionIds(Request $request)
    {
        // FIX: Retrieve parameter by Name
        $examId = $request->route('exam');

        $exam = Exam::findOrFail($examId);
        $this->authorizeInstructor($exam);

        $ids = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->pluck('question_id');

        return response()->json($ids);
    }

    public function fetchAvailableQuestions(Request $request)
    {
        // FIX: Retrieve parameter by Name
        $examId = $request->route('exam');
        $sectionId = $request->route('section'); // Not strictly needed for logic but part of route

        $exam = Exam::findOrFail($examId);
        $this->authorizeInstructor($exam);

        $perPage = $request->input('per_page', 10);

        $existingIdsInExam = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->pluck('question_id')
            ->toArray();

        $query = Question::with(['questionType:id,name,code', 'difficultyLevel:id,name', 'topic:id,name'])
            ->whereNotIn('id', $existingIdsInExam);

        // 🔥 FIX: Question Visibility for Instructors
        // if (Auth::user()->hasRole('instructor')) {
        //      $query->where(function($q) {
        //          // 1. Show Own Questions
        //          $q->where('created_by', Auth::id())
        //          // 2. OR Show Active Questions (Shared Bank)
        //           ->orWhere('is_active', 1);
        //      });
        // }
        if (Auth::user()->hasRole('instructor')) {
             $query->where('created_by', Auth::id());
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', '%' . $term . '%')
                    ->orWhere('question', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('qtype')) {
            $query->whereHas('questionType', function ($q) use ($request) {
                $q->where('code', $request->qtype);
            });
        }

        if ($request->filled('type')) $query->where('question_type_id', $request->type);
        if ($request->filled('difficulty')) $query->where('difficulty_level_id', $request->difficulty);
        if ($request->filled('topic')) $query->where('topic_id', $request->topic);
        if ($request->filled('skill')) $query->where('skill_id', $request->skill);

        // 🔥 FILTER: Comprehension
        if ($request->filled('is_comprehension') && $request->is_comprehension == 1) {
            $query->where('has_attachment', 1)
                  ->where('attachment_type', 'comprehension');
        }

        $questions = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($questions);
    }

    // --- ADDING QUESTIONS ---

    public function addQuestion(Request $request)
    {
        if ($request->has('question_id') && !$request->has('question_ids')) {
            $request->merge(['question_ids' => [$request->question_id]]);
        }
        return $this->bulkAddQuestions($request);
    }

    public function bulkAddQuestions(Request $request)
    {
        try {
            // FIX: Retrieve IDs safely by Name
            $examId = $request->route('exam');
            $sectionId = $request->route('section');

            $exam = Exam::findOrFail($examId);
            $section = ExamSection::where('id', $sectionId)->where('exam_id', $examId)->firstOrFail();

            $this->authorizeInstructor($exam);

            $questionIds = $request->input('question_ids', []);

            if (empty($questionIds)) {
                return response()->json(['status' => 'error', 'message' => 'No questions selected.']);
            }

            // Check existing to avoid duplicates
            $existing = DB::table('exam_questions')
                ->where('exam_id', $exam->id)
                ->whereIn('question_id', $questionIds)
                ->pluck('question_id')
                ->toArray();

            $newIds = array_diff($questionIds, $existing);

            $insertData = [];
            foreach ($newIds as $id) {
                $insertData[] = [
                    'exam_id' => $exam->id,
                    'exam_section_id' => $section->id,
                    'question_id' => $id
                ];
            }

            if (!empty($insertData)) {
                DB::table('exam_questions')->insert($insertData);
                $this->updateMeta($exam, $section); // Update totals
                return response()->json(['status' => 'success', 'message' => count($insertData) . ' Questions Added.']);
            }

            return response()->json(['status' => 'warning', 'message' => 'Selected questions are already in this exam.']);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // --- REMOVING QUESTIONS ---

    public function removeQuestion(Request $request)
    {
        if ($request->has('question_id') && !$request->has('question_ids')) {
            $request->merge(['question_ids' => [$request->question_id]]);
        }
        return $this->bulkRemoveQuestions($request);
    }

    public function bulkRemoveQuestions(Request $request)
    {
        try {
            // FIX: Retrieve IDs safely by Name
            $examId = $request->route('exam');
            $sectionId = $request->route('section');

            $exam = Exam::findOrFail($examId);
            $section = ExamSection::where('id', $sectionId)->where('exam_id', $examId)->firstOrFail();

            $this->authorizeInstructor($exam);

            $questionIds = $request->input('question_ids', []);

            if (empty($questionIds)) {
                return response()->json(['status' => 'error', 'message' => 'No questions selected.']);
            }

            $deletedCount = DB::table('exam_questions')
                ->where('exam_section_id', $section->id)
                ->whereIn('question_id', $questionIds)
                ->delete();

            if ($deletedCount > 0) {
                $this->updateMeta($exam, $section);
                return response()->json(['status' => 'success', 'message' => $deletedCount . ' question(s) removed successfully.']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Delete Failed.']);
            }

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function updateMeta($exam, $section) {
        if (method_exists($section, 'updateMeta')) $section->updateMeta();
        if (method_exists($exam, 'updateMeta')) $exam->updateMeta();
    }
}
