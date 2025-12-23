<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamScheduleRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExamScheduleController extends Controller
{
    public function index(Exam $exam) {
        $examSchedules = ExamSchedule::where('exam_id', $exam->id)->with('userGroups')->paginate(10);
        $userGroups = UserGroup::active()->get();
        return view('admin.exams.schedules.index', compact('exam', 'examSchedules', 'userGroups'));
    }

    public function store(StoreExamScheduleRequest $request, Exam $exam) {
        $schedule = new ExamSchedule($request->validated());
        $schedule->exam_id = $exam->id;

        // Handle Date/Time Logic from your repository
        $start = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $schedule->start_date = $start->toDateString();
        $schedule->start_time = $start->toTimeString();

        if($request->schedule_type == 'fixed') {
            $end = $start->addSeconds($exam->total_duration);
        } else {
            $end = Carbon::parse($request->end_date . ' ' . $request->end_time);
        }

        $schedule->end_date = $end->toDateString();
        $schedule->end_time = $end->toTimeString();
        $schedule->save();
        $schedule->userGroups()->sync($request->user_groups);

        return back()->with('success', 'Schedule Created!');
    }
}
