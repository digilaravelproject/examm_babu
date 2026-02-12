<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\UserGroup;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ExamScheduleController extends Controller
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
        // Allow Admins/Managers to bypass
        if (Auth::user()->hasRole('admin') || Auth::user()->can('manage exams')) {
            return;
        }

        // System protected check
        if (is_null($exam->created_by)) {
             abort(403, 'System protected exam.');
        }

        if ($exam->created_by != Auth::id()) {
            abort(403, 'You are not authorized to manage schedules for this exam.');
        }
    }

    private function calculateScheduleDates(Request $request, Exam $exam)
    {
        $startDate = Carbon::parse($request->start_date . ' ' . $request->start_time);

        if ($request->schedule_type == 'fixed') {
            // Safe Settings Access
            $settings = $exam->settings;
            if ($settings instanceof \Illuminate\Support\Collection) {
                $settings = $settings->all();
            } elseif (!is_array($settings)) {
                $settings = [];
            }
            
            // Calculate total seconds (default 1 hour if not set)
            $totalSeconds = $exam->total_duration ?? 3600; 
            
            $endDate = $startDate->copy()->addSeconds($totalSeconds);
        } else {
            $endDate = Carbon::parse($request->end_date . ' ' . $request->end_time);
        }

        return [
            'start_date' => $startDate->toDateString(),
            'start_time' => $startDate->toTimeString(),
            'end_date'   => $endDate->toDateString(),
            'end_time'   => $endDate->toTimeString(),
        ];
    }

    // --- MAIN CONTROLLER METHODS ---

    /**
     * List all exam schedules
     */
    public function index(Request $request)
    {
        // FIX: Explicitly fetch 'exam' param
        $examId = $request->route('exam'); 
        $exam = Exam::findOrFail($examId);

        $this->authorizeInstructor($exam);
        
         if ($exam->status !== 'published') {

        // Prepare Params for redirect
        $params = array_merge($this->getRouteParams(), ['exam' => $exam->id]);

        return redirect()
            ->route($this->getRoutePrefix() . 'exams.questions.index', $params)
            ->with('trigger_publish_modal', true);
    }

        $steps = $this->repository->getSteps($exam->id, 'schedules');

        $schedules = $exam->examSchedules()
            ->with('userGroups')
            ->withCount('sessions')
            ->latest()
            ->paginate(10);

        $userGroups = UserGroup::where('is_active', 1)->get();

        return view('admin.exams.schedules.index', compact('exam', 'steps', 'schedules', 'userGroups'));
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request)
    {
        // FIX: Explicitly fetch 'exam' param
        $examId = $request->route('exam');
        $exam = Exam::findOrFail($examId);

        $this->authorizeInstructor($exam);

        $request->validate([
            'schedule_type'  => 'required|in:fixed,flexible',
            'user_group_ids' => 'required|array',
            'start_date'     => 'required|date',
            'start_time'     => 'required',
            'end_date'       => 'required_if:schedule_type,flexible|nullable|date|after_or_equal:start_date',
            'end_time'       => 'required_if:schedule_type,flexible',
            'grace_period'   => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $schedule = new ExamSchedule();
            $schedule->exam_id = $exam->id;
            $schedule->status = $request->status ?? 'active';
            $schedule->grace_period = ($request->grace_period && $request->grace_period > 0) ? $request->grace_period : 5;
            $schedule->schedule_type = $request->schedule_type;
            $schedule->code = 'SCH-' . strtoupper(\Illuminate\Support\Str::random(8));

            $dates = $this->calculateScheduleDates($request, $exam);

            $schedule->start_date = $dates['start_date'];
            $schedule->start_time = $dates['start_time'];
            $schedule->end_date    = $dates['end_date'];
            $schedule->end_time    = $dates['end_time'];

            $schedule->save();

            if ($request->has('user_group_ids')) {
                $schedule->userGroups()->sync($request->user_group_ids);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Exam Schedule created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating schedule: ' . $e->getMessage());
        }
    }

    /**
     * Edit - Returns JSON for Modals
     * Important: Removed $id from arguments, using route('schedule')
     */
    public function edit(Request $request)
    {
        try {
            // FIX: Explicitly fetch 'schedule' param
            $scheduleId = $request->route('schedule');
            $schedule = ExamSchedule::with('userGroups')->findOrFail($scheduleId);
            
            // Check authorization via exam
            $exam = Exam::find($schedule->exam_id);
            if($exam) {
                $this->authorizeInstructor($exam);
            }

            $disableFlag = $schedule->status == 'expired';

            return response()->json([
                'schedule' => $schedule,
                'user_group_ids' => $schedule->userGroups->pluck('id'),
                'disableFlag' => $disableFlag
            ]);
        } catch (Exception $e) {
             return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing schedule
     * Important: Removed $id from arguments, using route('schedule')
     */
    public function update(Request $request)
    {
        // FIX: Explicitly fetch 'schedule' param
        $scheduleId = $request->route('schedule');
        $schedule = ExamSchedule::findOrFail($scheduleId);
        $exam = Exam::findOrFail($schedule->exam_id);

        $this->authorizeInstructor($exam);

        // Optional: Block update if expired (logic commented out in case you want to allow)
        // if ($schedule->status == 'expired') { ... }

        $request->validate([
            'schedule_type'  => 'required|in:fixed,flexible',
            'user_group_ids' => 'required|array',
            'start_date'     => 'required|date',
            'start_time'     => 'required',
        ]);

        DB::beginTransaction();
        try {
            $schedule->status = $request->status;
            $schedule->grace_period = ($request->grace_period && $request->grace_period > 0) ? $request->grace_period : 5;
            $schedule->schedule_type = $request->schedule_type;

            $dates = $this->calculateScheduleDates($request, $exam);

            $schedule->start_date = $dates['start_date'];
            $schedule->start_time = $dates['start_time'];
            $schedule->end_date    = $dates['end_date'];
            $schedule->end_time    = $dates['end_time'];

            $schedule->save();

            if ($request->has('user_group_ids')) {
                $schedule->userGroups()->sync($request->user_group_ids);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Exam Schedule updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating schedule: ' . $e->getMessage());
        }
    }

    /**
     * Destroy
     * Important: Removed $id from arguments, using route('schedule')
     */
    public function destroy(Request $request)
    {
        try {
            // FIX: Explicitly fetch 'schedule' param
            $scheduleId = $request->route('schedule');
            $schedule = ExamSchedule::findOrFail($scheduleId);
            $exam = Exam::find($schedule->exam_id);

            if($exam) {
                $this->authorizeInstructor($exam);
            }

            $schedule->userGroups()->detach();
            $schedule->delete();
            return redirect()->back()->with('success', 'Exam Schedule deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Unable to delete schedule.');
        }
    }

    /**
     * Analytics Page Logic
     * Important: Removed $id from arguments, using route('schedule')
     */
    public function analytics(Request $request)
    {
        // FIX: Explicitly fetch 'schedule' param
        $scheduleId = $request->route('schedule');
        
        // Eager load exam to pass to view
        $schedule = ExamSchedule::with('exam')->findOrFail($scheduleId);
        $exam = $schedule->exam;

        $this->authorizeInstructor($exam);

        // Check relationship existance before accessing
        if (method_exists($schedule, 'sessions')) {
             $sessions = $schedule->sessions;
        } else {
             // Fallback query if relation missing
             $sessions = \App\Models\ExamSession::where('exam_schedule_id', $schedule->id)->get();
        }

        $totalAttempts = $sessions->count();

        $stats = [
            'total_attempts' => $totalAttempts,
            'pass_attempts' => $sessions->where('status', 'passed')->count(),
            'fail_attempts' => $sessions->where('status', 'failed')->count(),
            'unique_takers' => $sessions->unique('user_id')->count(),

            'avg_time' => $totalAttempts > 0 ? round($sessions->avg('total_time_taken') / 60, 2) : 0,
            'avg_score' => $totalAttempts > 0 ? round($sessions->avg('score'), 2) : 0,
            'high_score' => $sessions->max('score') ?? 0,
            'low_score' => $sessions->min('score') ?? 0,

            'avg_percentage' => $totalAttempts > 0 ? round($sessions->avg('percentage'), 2) : 0,
            'avg_accuracy' => $totalAttempts > 0 ? round($sessions->avg('accuracy'), 2) : 0,
            'avg_speed' => $totalAttempts > 0 ? round($sessions->avg('speed'), 2) : 0,
            'avg_answered' => $totalAttempts > 0 ? round($sessions->avg('total_answered'), 0) : 0,
        ];

        // FIX: Pass 'exam' to view to fix "Undefined variable $exam" error
        return view('admin.exams.schedules.analytics', compact('schedule', 'stats', 'exam'));
    }
}