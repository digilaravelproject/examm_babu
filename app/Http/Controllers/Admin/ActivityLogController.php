<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Saare logs fetch karo, latest pehle
        $logs = Activity::with('causer')->latest()->paginate(20);

        return view('admin.logs.index', compact('logs'));
    }
}
