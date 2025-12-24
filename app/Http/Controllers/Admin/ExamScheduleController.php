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

    public function index(Exam $exam)
    {
        $steps = $this->repository->getSteps($exam->id, 'schedules');
        $schedules = $exam->examSchedules()->with('userGroups')->latest()->paginate(10);
        $userGroups = UserGroup::where('is_active', 1)->get();

        return view('admin.exams.schedules.index', compact('exam', 'steps', 'schedules', 'userGroups'));
    }

    public function store(Request $request, Exam $exam)
    {
        $request->validate([
            'schedule_type' => 'required|in:fixed,flexible',
            'user_group_ids' => 'required|array',
            // Add date validations
        ]);

        DB::beginTransaction();
        try {
            $schedule = new ExamSchedule();
            $schedule->exam_id = $exam->id;
            $schedule->schedule_type = $request->schedule_type;
            $schedule->grace_period = $request->grace_period ?? 0;
            $schedule->status = 'active'; // Default status

            if ($request->schedule_type == 'fixed') {
                // Combine Date and Time inputs
                $schedule->start_date = $request->start_date;
                $schedule->start_time = $request->start_time;

                // Calculate End Time based on Exam Duration
                $startDateTime = Carbon::parse($request->start_date . ' ' . $request->start_time);
                $endDateTime = $startDateTime->copy()->addSeconds($exam->total_duration);

                $schedule->end_date = $endDateTime->format('Y-m-d');
                $schedule->end_time = $endDateTime->format('H:i:s');
            } else {
                // Flexible
                $schedule->start_date = $request->start_date;
                $schedule->start_time = $request->start_time;
                $schedule->end_date = $request->end_date;
                $schedule->end_time = $request->end_time;
            }

            $schedule->save();
            $schedule->userGroups()->sync($request->user_group_ids);

            DB::commit();
            return back()->with('success', 'Schedule Created Successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating schedule: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Exam $exam, ExamSchedule $schedule)
    {
        // Similar logic to store, but using $schedule->update()
        // Ensure you detach/sync user groups
        $schedule->userGroups()->sync($request->user_group_ids);
        return back()->with('success', 'Schedule Updated');
    }

    public function destroy(Exam $exam, ExamSchedule $schedule)
    {
        $schedule->userGroups()->detach();
        $schedule->delete();
        return back()->with('success', 'Schedule Deleted');
    }
}
