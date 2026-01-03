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
use Exception;

class ExamScheduleController extends Controller
{
    private $repository;

    public function __construct(ExamRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all exam schedules
     */
    public function index(Exam $exam)
    {
        if (!$exam->is_active) {
            return redirect()->back()->with('error', 'Exam is in draft mode. Kindly publish the exam before scheduling it.');
        }

        $steps = $this->repository->getSteps($exam->id, 'schedules');

        $schedules = $exam->examSchedules()
            ->with('userGroups')
            ->latest()
            ->paginate(10);

        $userGroups = UserGroup::where('is_active', 1)->get();

        return view('admin.exams.schedules.index', compact('exam', 'steps', 'schedules', 'userGroups'));
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request, Exam $exam)
    {
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
            // Default to 5 if provided value is 0 or empty
            $schedule->grace_period = ($request->grace_period && $request->grace_period > 0) ? $request->grace_period : 5;
            $schedule->schedule_type = $request->schedule_type;

            $dates = $this->calculateScheduleDates($request, $exam);

            $schedule->start_date = $dates['start_date'];
            $schedule->start_time = $dates['start_time'];
            $schedule->end_date   = $dates['end_date'];
            $schedule->end_time   = $dates['end_time'];

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
     */
    public function edit(Exam $exam, ExamSchedule $schedule)
    {
        // Load relationship properly
        $schedule->load('userGroups');

        $now = Carbon::now();

        // FIX: Date ko pehle parse karein, phir Time set karein (String concatenation avoid karein)
        $startDateTime = Carbon::parse($schedule->start_date)->setTimeFromTimeString($schedule->start_time);

        $startsIn = $now->diffInSeconds($startDateTime, false);

        // Logic: Cannot edit if expired
        $disableFlag = $schedule->status == 'expired';

        return response()->json([
            'schedule' => $schedule,
            'user_group_ids' => $schedule->userGroups->pluck('id'),
            'disableFlag' => $disableFlag
        ]);
    }

    /**
     * Update an existing schedule
     */
    public function update(Request $request, Exam $exam, $id)
    {
        $schedule = ExamSchedule::findOrFail($id);

        if ($schedule->status == 'expired') {
            return redirect()->back()->with('error', "You can't update once the exam schedule starts or is expired.");
        }

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
            $schedule->end_date   = $dates['end_date'];
            $schedule->end_time   = $dates['end_time'];

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

    public function destroy(Exam $exam, $id)
    {
        try {
            $schedule = ExamSchedule::findOrFail($id);
            $schedule->userGroups()->detach();
            $schedule->delete();
            return redirect()->back()->with('success', 'Exam Schedule deleted successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Unable to delete schedule.');
        }
    }

    private function calculateScheduleDates(Request $request, Exam $exam)
    {
        // Use Carbon::parse to handle date and time without requiring exact 'H:i:s' format
        $startDate = Carbon::parse($request->start_date . ' ' . $request->start_time);

        if ($request->schedule_type == 'fixed') {
            $endDate = $startDate->copy()->addSeconds($exam->total_duration);
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
}
