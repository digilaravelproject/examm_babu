<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\Exam;
use App\Models\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // --- 1. CORE STATS ---
        $currentUsers = User::count();
        $lastMonthUsers = User::where('created_at', '<=', now()->subMonth())->count();

        // Revenue Column Check
        $revenueCol = Schema::hasColumn('payments', 'total_amount') ? 'total_amount' : 'amount';

        $currentRevenue = Payment::where('status', 'success')->sum($revenueCol);
        $lastMonthRevenue = Payment::where('status', 'success')
            ->where('created_at', '<=', now()->subMonth())
            ->sum($revenueCol);

        $stats = [
            'total_users'    => $currentUsers,
            'user_growth'    => $this->calculateGrowth($currentUsers, $lastMonthUsers),
            'total_revenue'  => $currentRevenue,
            'revenue_growth' => $this->calculateGrowth($currentRevenue, $lastMonthRevenue),
            'active_subs'    => Subscription::where('ends_at', '>', now())->count(),
            'total_content'  => Exam::count() + Question::count(),
        ];

        // --- 2. RECENT DATA ---
        $recentUsers = User::latest()->take(5)->get();
        $recentActivities = Activity::with('causer')->latest()->take(6)->get();

        $topGroups = UserGroup::withCount('users')
            ->orderBy('users_count', 'desc')
            ->take(4)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentActivities', 'topGroups'));
    }

    /**
     * AJAX Chart Data Handler
     */
    public function getChartData(Request $request)
    {
        $range = $request->get('range', '30_days');
        $revenueCol = Schema::hasColumn('payments', 'total_amount') ? 'total_amount' : 'amount';

        $queryRevenue = Payment::where('status', 'success');
        $queryExam = ExamSession::query();

        $startDate = now();
        $groupBy = 'date'; // Default group by Date
        $dateFormat = 'Y-m-d'; // Default format

        // Logic to handle Time Ranges
        switch ($range) {
            case 'today':
                $startDate = now()->startOfDay();
                $groupBy = 'hour'; // Group by Hour for Today
                $dateFormat = 'H:00'; // Format: 14:00
                break;
            case '15_days':
                $startDate = now()->subDays(15);
                $dateFormat = 'd M';
                break;
            case '30_days':
                $startDate = now()->subDays(30);
                $dateFormat = 'd M';
                break;
            case '3_months':
                $startDate = now()->subMonths(3);
                $dateFormat = 'd M Y';
                break;
            case '6_months':
                $startDate = now()->subMonths(6);
                $dateFormat = 'M Y';
                break;
            case '1_year':
                $startDate = now()->subYear();
                $dateFormat = 'M Y';
                break;
            case 'lifetime':
                $startDate = Carbon::create(2000, 1, 1);
                $dateFormat = 'M Y';
                break;
        }

        // --- FETCH REVENUE DATA ---
        if ($groupBy === 'hour') {
            // SQLite/MySQL compatible Hour extraction (Adjust based on DB driver if strictly needed)
            // Using generic approach for MySQL
            $revenueData = $queryRevenue->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('HOUR(created_at) as label'),
                    DB::raw("SUM($revenueCol) as total")
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            $examData = $queryExam->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('HOUR(created_at) as label'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            // Format Labels for Hour (e.g., 1 -> 01:00 AM)
            $revenueLabels = $revenueData->map(fn($item) => Carbon::createFromTime($item->label, 0)->format('h A'));
            $examLabels = $examData->map(fn($item) => Carbon::createFromTime($item->label, 0)->format('h A'));

        } else {
            // Standard Date Grouping
            $revenueData = $queryRevenue->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(created_at) as label'),
                    DB::raw("SUM($revenueCol) as total")
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            $examData = $queryExam->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(created_at) as label'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            // Format Labels for Date
            $revenueLabels = $revenueData->map(fn($item) => Carbon::parse($item->label)->format($dateFormat));
            $examLabels = $examData->map(fn($item) => Carbon::parse($item->label)->format($dateFormat));
        }

        return response()->json([
            'revenue' => [
                'labels' => $revenueLabels,
                'data'   => $revenueData->pluck('total'),
            ],
            'exams' => [
                'labels' => $examLabels,
                'data'   => $examData->pluck('count'),
            ]
        ]);
    }

    /**
     * Clear Cache & Optimize System
     */
   public function optimize()
    {
        try {
            Artisan::call('optimize:clear');
            // JSON response return kar rahe hain taaki page reload na ho
            return response()->json([
                'success' => true,
                'message' => 'System optimized and cache cleared successfully! 🚀'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return 100;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
