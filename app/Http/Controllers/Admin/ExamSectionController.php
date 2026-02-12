<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Section;
use App\Models\Question;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ExamSectionController extends Controller
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
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->can('manage exams')) {
            return;
        }

        if (is_null($exam->created_by)) {
            abort(403, 'System protected exam. You cannot modify it.');
        }

        if ($exam->created_by != $user->id) {
            abort(403, 'You are not authorized to modify this exam.');
        }
    }

    // --- METHODS ---

    public function index(Request $request)
    {
        $examId = $request->route('exam');
        $exam = Exam::with(['examSections' => function ($query) {
            $query->with('section:id,name')
                ->withCount('questions')
                ->orderBy('section_order');
        }])->findOrFail($examId);

        $this->authorizeInstructor($exam);

        $availableSections = Section::where('is_active', 1)
            ->with(['skills' => function ($q) {
                $q->where('is_active', 1)
                    ->select('id', 'name', 'section_id')
                    ->with(['topics' => function ($subQ) {
                        $subQ->where('is_active', 1)->select('id', 'name', 'skill_id');
                    }]);
            }])
            ->select('id', 'name')
            ->get();

        $steps = $this->repository->getSteps($exam->id, 'sections');

        return view('admin.exams.sections.index', compact('exam', 'availableSections', 'steps'));
    }

    public function store(Request $request)
    {
        $examId = $request->route('exam');
        $exam = Exam::findOrFail($examId);

        $this->authorizeInstructor($exam);

        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'section_order' => 'required|integer',
            'correct_marks' => 'required|numeric|min:0',
            'negative_marks' => 'nullable|numeric|min:0',
            'negative_marking_type' => 'nullable|in:fixed,percentage',
            'section_cutoff' => 'nullable|numeric|min:0|max:100',
            'total_duration' => 'nullable|integer|min:0',
            'selected_topics' => 'nullable|array',
            'selected_topics.*' => 'exists:topics,id',
            'allow_translation' => 'nullable|boolean',
            // Translation validation
            'translation_language' => 'nullable|required_if:allow_translation,1|in:hi,mr',
        ]);

        DB::beginTransaction();
        try {
            $examSection = new ExamSection();
            $examSection->exam_id = $exam->id;
            $examSection->section_id = $request->section_id;
            $examSection->name = $request->name;
            $examSection->section_order = $request->section_order;

            // Scoring
            $examSection->correct_marks = $request->correct_marks;
            $examSection->negative_marking_type = $request->negative_marking_type ?? 'fixed';
            $examSection->negative_marks = $request->negative_marks ?? 0;
            $examSection->section_cutoff = $request->section_cutoff ?? 0;

            // Translation Logic
            $examSection->allow_translation = $request->has('allow_translation') ? 1 : 0;
            $examSection->translation_language = $examSection->allow_translation ? $request->translation_language : null;

            // Duration
            $autoDuration = true;
            if (is_array($exam->settings)) {
                $autoDuration = $exam->settings['auto_duration'] ?? true;
            } elseif (is_object($exam->settings) && method_exists($exam->settings, 'get')) {
                $autoDuration = $exam->settings->get('auto_duration', true);
            }

            if ($autoDuration) {
                $examSection->total_duration = 0;
            } else {
                $examSection->total_duration = ($request->total_duration ?? 0) * 60;
            }
            $examSection->total_marks = 0;

            $examSection->save();

            if ($request->has('import_questions') && $request->import_questions == 1) {
                $topicIds = $request->selected_topics ?? [];
                $this->syncQuestionsByTopics($exam->id, $examSection->id, $request->section_id, $topicIds, true);
            }

            $this->recalculateSectionTotals($exam, $examSection);

            if (method_exists($exam, 'updateMeta')) {
                $exam->updateMeta();
            }

            DB::commit();

            $redirectParams = array_merge($this->getRouteParams(), ['exam' => $exam->id]);
            return redirect()->route($this->getRoutePrefix() . 'exams.sections.index', $redirectParams)
                ->with('success', 'Exam Section added successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Error adding section: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Request $request, $examId, $sectionId = null)
    {
        $realSectionId = $sectionId ? $sectionId : $examId;

        try {
            $section = ExamSection::with(['section:id,name', 'exam'])->findOrFail($realSectionId);
            $this->authorizeInstructor($section->exam);

            $importedData = DB::table('exam_questions')
                ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
                ->where('exam_questions.exam_section_id', $realSectionId)
                ->distinct()
                ->get(['questions.skill_id', 'questions.topic_id']);

            $importedSkillIds = $importedData->pluck('skill_id')->filter()->unique()->values()->toArray();
            $importedTopicIds = $importedData->pluck('topic_id')->filter()->unique()->values()->toArray();

            $duration_minutes = $section->total_duration > 0 ? floor($section->total_duration / 60) : 0;

            return response()->json([
                'id' => $section->id,
                'name' => $section->name,
                'section_id' => $section->section_id,
                'section_order' => $section->section_order,
                'correct_marks' => $section->correct_marks,
                'negative_marks' => $section->negative_marks,
                'negative_marking_type' => $section->negative_marking_type,
                'section_cutoff' => $section->section_cutoff,
                'allow_translation' => $section->allow_translation,
                'translation_language' => $section->translation_language, // Added this
                'total_duration' => $section->total_duration,
                'duration_minutes' => $duration_minutes,
                'imported_skill_ids' => $importedSkillIds,
                'imported_topic_ids' => $importedTopicIds,
                'has_imported_questions' => count($importedTopicIds) > 0
            ]);
        } catch (Exception $e) {
            Log::error('Edit Section Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $examId, $sectionId = null)
    {
        $realSectionId = $sectionId ? $sectionId : $examId;

        $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'section_order' => 'required|integer',
            'correct_marks' => 'required|numeric|min:0',
            'selected_topics' => 'nullable|array',
            // Translation validation
            'translation_language' => 'nullable|required_if:allow_translation,1|in:hi,mr',
        ]);

        DB::beginTransaction();
        try {
            $examSection = ExamSection::with('exam')->findOrFail($realSectionId);
            $exam = $examSection->exam;

            $this->authorizeInstructor($exam);

            $examSection->fill($request->only([
                'name',
                'section_id',
                'section_order',
                'correct_marks',
                'negative_marking_type',
                'negative_marks',
                'section_cutoff'
            ]));

            // Translation Logic
            $examSection->allow_translation = $request->has('allow_translation') ? 1 : 0;
            $examSection->translation_language = $examSection->allow_translation ? $request->translation_language : null;

            $examSection->save();

            $shouldImport = $request->has('import_questions') && $request->import_questions == 1;
            $topicIds = $request->selected_topics ?? [];

            $this->syncQuestionsByTopics($exam->id, $examSection->id, $request->section_id, $topicIds, $shouldImport);
            $this->recalculateSectionTotals($exam, $examSection);

            if (method_exists($examSection, 'updateMeta')) {
                $examSection->updateMeta();
            }
            if (method_exists($exam, 'updateMeta')) {
                $exam->updateMeta();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Exam Section updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $examId, $sectionId = null)
    {
        $realSectionId = $sectionId ? $sectionId : $examId;

        try {
            $examSection = ExamSection::withCount(['examSessions'])->findOrFail($realSectionId);
            $exam = Exam::findOrFail($examSection->exam_id);

            $this->authorizeInstructor($exam);

            if (method_exists($examSection, 'canSecureDelete') && !$examSection->canSecureDelete('examSessions')) {
                $count = $examSection->exam_sessions_count;
                return redirect()->back()->with('error', "Cannot delete section. It is associated with $count exam sessions.");
            }

            DB::transaction(function () use ($examSection, $exam) {
                if (method_exists($examSection, 'questions')) {
                    $examSection->questions()->detach();
                }

                if (method_exists($examSection, 'secureDelete')) {
                    $examSection->secureDelete('examSessions');
                } else {
                    $examSection->delete();
                }

                if (method_exists($exam, 'updateMeta')) {
                    $exam->updateMeta();
                }
            });

            $redirectParams = array_merge($this->getRouteParams(), ['exam' => $exam->id]);
            return redirect()->route($this->getRoutePrefix() . 'exams.sections.index', $redirectParams)
                ->with('success', 'Section successfully deleted!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Database Error: Unable to delete section. It might be in use.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting section: ' . $e->getMessage());
        }
    }

    // --- Private Methods ---

    private function syncQuestionsByTopics($examId, $examSectionId, $masterSectionId, $selectedTopicIds, $shouldImport)
    {
        $query = Question::query();

        if (!empty($selectedTopicIds)) {
            $query->whereIn('topic_id', $selectedTopicIds);
        } else {
            $query->whereHas('skill', function ($q) use ($masterSectionId) {
                $q->where('section_id', $masterSectionId);
            });
        }

        $questionIds = $query->pluck('id')->toArray();

        if (empty($questionIds)) return;

        if ($shouldImport) {
            $existing = DB::table('exam_questions')
                ->where('exam_id', $examId)
                ->whereIn('question_id', $questionIds)
                ->pluck('question_id')
                ->toArray();

            $newQuestions = array_diff($questionIds, $existing);

            $insertData = [];
            foreach ($newQuestions as $qId) {
                $insertData[] = [
                    'exam_id' => $examId,
                    'exam_section_id' => $examSectionId,
                    'question_id' => $qId
                ];
            }

            if (!empty($insertData)) {
                DB::table('exam_questions')->insert($insertData);
            }
        } else {
            DB::table('exam_questions')
                ->where('exam_section_id', $examSectionId)
                ->whereIn('question_id', $questionIds)
                ->delete();
        }
    }

    private function recalculateSectionTotals($exam, $examSection)
    {
        $autoDuration = true;
        $autoGrading = true;

        if (is_array($exam->settings)) {
            $autoDuration = $exam->settings['auto_duration'] ?? true;
            $autoGrading = $exam->settings['auto_grading'] ?? true;
        } elseif (is_object($exam->settings) && method_exists($exam->settings, 'get')) {
            $autoDuration = $exam->settings->get('auto_duration', true);
            $autoGrading = $exam->settings->get('auto_grading', true);
        }

        $examSection->load('questions');

        if ($autoDuration) {
            $examSection->total_duration = $examSection->questions()->sum('default_time');
        }

        if ($autoGrading) {
            $examSection->total_marks = $examSection->questions()->sum('default_marks');
        } else {
            $examSection->total_marks = $examSection->questions()->count() * $examSection->correct_marks;
        }

        $examSection->save();

        if (method_exists($examSection, 'updateMeta')) {
            $examSection->updateMeta();
        }
    }
}
