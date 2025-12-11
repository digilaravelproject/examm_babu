<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Dashboard par stats dikhane ke liye
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $recentActivities = Activity::with('causer')->latest()->take(5)->get();

        return view('admin.dashboard', compact('totalUsers', 'totalRoles', 'recentActivities'));
    }
}
