@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    {{--
        ========================================
        🎨 THEME & CSS VARIABLES (Exam Babu Brand)
        ========================================
    --}}
    <style>


        /* Utility overrides using variables */
        .text-primary { color: var(--brand-blue) !important; }
        .bg-primary { background-color: var(--brand-blue) !important; }

        /* Light variations for backgrounds */
        .bg-primary-light { background-color: rgba(7, 119, 190, 0.1) !important; }
        .text-brand-green { color: var(--brand-green) !important; }
        .bg-brand-green-light { background-color: rgba(148, 201, 64, 0.1) !important; }

        /* Fix Horizontal Scroll & Layout */
        body, html { overflow-x: hidden; }
        .wrapper-fix { width: 100%; max-width: 100%; overflow-x: hidden; }
        .apexcharts-canvas { margin: 0 auto; }

        /* Custom Scrollbar for Tables */
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background-color: #f1f5f9; }
    </style>

    {{-- SCRIPTS: ApexCharts & SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Main Wrapper with AlpineJS --}}
    <div class="min-h-screen px-4 py-8 mx-auto wrapper-fix max-w-7xl sm:px-6 lg:px-8"
         x-data="dashboardData()">

        {{-- HEADER SECTION --}}
        <div class="flex flex-col justify-between mb-8 md:flex-row md:items-center">
            <div class="flex items-center gap-4">
                {{-- Admin Avatar Logic --}}
                <div class="relative flex-shrink-0">
                    @if(Auth::user()->profile_photo_url)
                        <img class="object-cover w-16 h-16 border-4 border-white rounded-full shadow-md"
                             src="{{ Auth::user()->profile_photo_url }}"
                             alt="{{ Auth::user()->first_name }}">
                    @else
                        {{-- Updated Gradient to Exam Babu Blue -> Pink --}}
                        <div class="flex items-center justify-center w-16 h-16 text-xl font-bold text-white uppercase border-4 border-white rounded-full shadow-md bg-gradient-to-br from-[#0777be] to-[#f062a4]">
                            {{ substr(Auth::user()->first_name ?? 'A', 0, 1) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-4 h-4 border-2 border-white rounded-full bg-[#94c940]"></span>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Hello, {{ Auth::user()->first_name ?? 'Admin' }}! 👋
                    </h1>
                    <p class="text-sm text-gray-500">Overview of your platform performance.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
                {{-- Date Badge --}}
                <span class="flex items-center px-4 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg shadow-sm">
                    📅 {{ now()->format('d M Y') }}
                </span>

                {{-- OPTIMIZE BUTTON --}}
                <button @click="optimizeSystem"
                        :disabled="optimizing"
                        class="flex items-center gap-1 px-4 py-2 text-xs font-semibold text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">

                    <template x-if="optimizing">
                        <svg class="w-4 h-4 text-gray-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>

                    <template x-if="!optimizing">
                        {{-- Used Green for Icon --}}
                        <svg class="w-4 h-4 text-[#94c940]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </template>

                    <span x-text="optimizing ? 'Cleaning...' : 'Optimize System'"></span>
                </button>

                {{-- Add User Button (Primary Blue) --}}
                <a href="{{ route('admin.users.create') }}" class="flex items-center px-4 py-2 text-xs font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:opacity-90">
                    + Add Student
                </a>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">

            {{-- 1. Total Revenue (Primary Blue) --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Total Revenue</p>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">₹{{ number_format($stats['total_revenue']) }}</h3>
                    </div>
                    <div class="p-3 transition-colors rounded-lg bg-[#0777be]/10 text-[#0777be] group-hover:bg-[#0777be] group-hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="flex items-center mt-4 text-xs">
                    {{-- Growth positive uses Green --}}
                    <span class="flex items-center font-medium {{ $stats['revenue_growth'] >= 0 ? 'text-[#94c940]' : 'text-red-600' }}">
                        {{ $stats['revenue_growth'] }}%
                        <span class="ml-1 text-gray-400">vs last month</span>
                    </span>
                </div>
            </div>

            {{-- 2. Total Students (Sky Blue) --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Total Students</p>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</h3>
                    </div>
                    <div class="p-3 transition-colors rounded-lg bg-[#7fd2ea]/20 text-[#0777be] group-hover:bg-[#7fd2ea] group-hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <div class="flex items-center mt-4 text-xs">
                    <span class="flex items-center font-medium text-[#94c940]">
                        +{{ $stats['user_growth'] }}%
                        <span class="ml-1 text-gray-400">new joiners</span>
                    </span>
                </div>
            </div>

            {{-- 3. Active Plans (Pink) --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Active Plans</p>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['active_subs'] }}</h3>
                    </div>
                    <div class="p-3 transition-colors rounded-lg bg-[#f062a4]/10 text-[#f062a4] group-hover:bg-[#f062a4] group-hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-[#f062a4] h-1.5 rounded-full" style="width: 70%"></div>
                </div>
            </div>

            {{-- 4. Content DB (Green) --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Content DB</p>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total_content']) }}</h3>
                    </div>
                    <div class="p-3 transition-colors rounded-lg bg-[#94c940]/10 text-[#94c940] group-hover:bg-[#94c940] group-hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">Exams + Questions count</div>
            </div>
        </div>

        {{-- CHARTS & TABLES --}}
        <div class="flex flex-col gap-8 lg:flex-row">

            {{-- LEFT COLUMN: Charts & Table --}}
            <div class="w-full space-y-8 lg:w-3/4">

                {{-- Financial Overview --}}
                <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl relative min-h-[400px]">
                    {{-- Loader --}}
                    <div x-show="loading" class="absolute inset-0 z-20 flex items-center justify-center transition-opacity duration-300 bg-white/80 rounded-xl">
                        <div class="flex flex-col items-center">
                            <svg class="w-8 h-8 mb-2 animate-spin text-[#0777be]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs font-medium text-gray-500">Updating Data...</span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between gap-4 mb-6 sm:flex-row sm:items-center">
                        <h3 class="text-lg font-bold text-gray-800">Financial Overview</h3>
                        <select x-model="selectedRange" @change="fetchData()"
                                class="block w-full py-2 pl-3 pr-10 text-sm transition border-gray-300 rounded-md shadow-sm cursor-pointer sm:w-auto bg-gray-50 focus:ring-[#0777be] focus:border-[#0777be] hover:bg-gray-100">
                            <option value="today">Today (Hourly)</option>
                            <option value="15_days">Last 15 Days</option>
                            <option value="30_days">Last 30 Days</option>
                            <option value="3_months">Last 3 Months</option>
                            <option value="6_months">Last 6 Months</option>
                            <option value="1_year">Last 1 Year</option>
                            <option value="lifetime">Lifetime</option>
                        </select>
                    </div>
                    <div id="revenueChart" class="w-full"></div>
                </div>

                {{-- Recent Users Table --}}
                <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex items-center justify-between p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">New Registrations</h3>
                        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-primary hover:underline">View All</a>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="text-xs font-semibold text-gray-500 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentUsers as $user)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            {{-- User Initials with Primary Color --}}
                                            <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-xs font-bold uppercase rounded-full text-[#0777be] bg-[#0777be]/10">
                                                {{ substr($user->first_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded">
                                            {{ $user->roles->first()?->name ?? 'Student' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full text-[#94c940] bg-[#94c940]/10">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#94c940]"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-700 rounded-full bg-red-50">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500">
                                        {{ $user->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Sidebar Stats --}}
            <div class="w-full space-y-6 lg:w-1/4">

                {{-- Exam Traffic --}}
                <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Exam Traffic</h3>
                    <div id="activityChart" class="min-h-[150px]"></div>
                    <p class="mt-2 text-xs text-center text-gray-400">Attempts in selected range</p>
                </div>

                {{-- Top Batches --}}
                <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Top Batches</h3>
                    <div class="space-y-4">
                        @foreach($topGroups as $group)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-gray-600 bg-gray-100 rounded-lg">
                                        {{ substr($group->name, 0, 1) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="w-32 text-sm font-medium text-gray-800 truncate">{{ $group->name }}</p>
                                        <p class="text-[10px] text-gray-500">{{ $group->code }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold text-gray-700 border border-gray-200 rounded bg-gray-50">
                                    {{ $group->users_count }} 👤
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Live Activity --}}
                <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Live Activity</h3>
                    <div class="relative ml-2 space-y-6 border-l-2 border-gray-100">
                        @foreach($recentActivities as $activity)
                            <div class="relative pl-6">
                                <div class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full bg-gray-300 ring-4 ring-white"></div>
                                <p class="text-sm font-semibold leading-snug text-gray-800">{{ $activity->description }}</p>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    <span class="font-medium text-primary">{{ $activity->causer->first_name ?? 'System' }}</span> • {{ $activity->created_at->diffForHumans(null, true) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPTS LOGIC --}}
    <script>
        // 1. SweetAlert Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // 2. Trigger Toast on Session Flash Messages
        @if(session('success'))
            Toast.fire({ icon: 'success', title: '{{ session('success') }}' });
        @endif

        @if(session('error'))
            Toast.fire({ icon: 'error', title: '{{ session('error') }}' });
        @endif

        // 3. AlpineJS Dashboard Logic
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardData', () => ({
                selectedRange: '30_days',
                loading: false,
                optimizing: false,
                revenueChart: null,
                activityChart: null,

                init() {
                    this.initCharts();
                    this.fetchData();
                },

                // AJAX OPTIMIZE FUNCTION
                optimizeSystem() {
                    this.optimizing = true;

                    fetch('{{ route('admin.system.optimize') }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Toast.fire({ icon: 'success', title: data.message });
                                setTimeout(() => { window.location.reload(); }, 2000);
                            } else {
                                Toast.fire({ icon: 'error', title: data.message });
                                this.optimizing = false;
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Toast.fire({ icon: 'error', title: 'Something went wrong!' });
                            this.optimizing = false;
                        });
                },

                fetchData() {
                    this.loading = true;
                    fetch(`{{ route('admin.dashboard.chart') }}?range=${this.selectedRange}`)
                        .then(res => res.json())
                        .then(data => {
                            this.updateCharts(data);
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.loading = false;
                        });
                },

                initCharts() {
                    // Defined Brand Colors from Code
                    const brandBlue = '#0777be';
                    const brandPink = '#f062a4';
                    const brandGreen = '#94c940';

                    // Revenue Chart (Primary Blue)
                    var revenueOptions = {
                        series: [],
                        chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true } },
                        colors: [brandBlue],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        xaxis: { type: 'category', labels: { style: { fontSize: '12px', colors: '#6b7280' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 90, 100] } },
                        noData: { text: 'Loading Data...' }
                    };
                    this.revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
                    this.revenueChart.render();

                    // Activity Chart (Pink for Contrast or Green)
                    // Let's use Pink for Exam Traffic to distinguish from money
                    var activityOptions = {
                        series: [],
                        chart: { type: 'bar', height: 200, toolbar: { show: false }, sparkline: { enabled: false } },
                        colors: [brandPink],
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
                        tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: () => 'Attempts: ' } }, marker: { show: false } },
                        xaxis: { crosshairs: { width: 1 } },
                        noData: { text: 'Loading...' }
                    };
                    this.activityChart = new ApexCharts(document.querySelector("#activityChart"), activityOptions);
                    this.activityChart.render();
                },

                updateCharts(data) {
                    // Update Revenue
                    this.revenueChart.updateOptions({ xaxis: { categories: data.revenue.labels } });
                    this.revenueChart.updateSeries([{ name: 'Revenue', data: data.revenue.data }]);

                    // Update Activity
                    this.activityChart.updateOptions({ xaxis: { categories: data.exams.labels } });
                    this.activityChart.updateSeries([{ name: 'Attempts', data: data.exams.data }]);
                }
            }));
        });
    </script>
@endsection
