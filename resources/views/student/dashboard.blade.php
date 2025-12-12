@extends('layouts.candidate')

@section('content')
    <!-- Welcome Header -->
    <div class="flex flex-col justify-between mb-8 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}! 👋</h1>
            <p class="mt-1 text-sm text-gray-500">Your exam preparation summary for today.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="px-3 py-1 text-xs font-medium text-blue-700 border border-blue-100 rounded-full bg-blue-50">
                Last Login: {{ now()->setTimezone('Asia/Kolkata')->format('d M, h:i A') }}
            </span>
        </div>
    </div>

    <!-- Stats Grid (Fixed Layout) -->
    <!-- Using lg:grid-cols-4 ensures it fits on laptop screens without stacking -->
    <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4">

        <!-- Total Tests -->
        <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Tests</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">12</p>
                </div>
                <div class="p-3 text-blue-600 rounded-lg bg-blue-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4 text-xs text-green-600">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span>+2 this week</span>
            </div>
        </div>

        <!-- Average Score -->
        <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Avg. Score</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">76.5%</p>
                </div>
                <div class="p-3 text-green-600 rounded-lg bg-green-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4 text-xs text-green-600">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18">
                    </path>
                </svg>
                <span>Top 15%</span>
            </div>
        </div>

        <!-- Global Rank -->
        <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Global Rank</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">#42</p>
                </div>
                <div class="p-3 text-yellow-600 rounded-lg bg-yellow-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4 text-xs text-gray-400">
                <span>Out of 1,240 students</span>
            </div>
        </div>

        <!-- Accuracy -->
        <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Accuracy</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">88%</p>
                </div>
                <div class="p-3 text-purple-600 rounded-lg bg-purple-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-4">
                <div class="bg-purple-600 h-1.5 rounded-full" style="width: 88%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Split (Left 70%, Right 30%) -->
    <div class="flex flex-col gap-8 lg:flex-row">

        <!-- Left Column: Banner & Tests -->
        <div class="w-full space-y-8 lg:w-3/4">

            <!-- Action Banner -->
            <div
                class="relative overflow-hidden text-white shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl">
                <div class="relative z-10 flex flex-col items-start justify-between p-8 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Mock Test Series 2025</h2>
                        <p class="max-w-md mt-2 text-blue-100">Practice with our latest pattern questions tailored for SSC
                            CGL & Banking exams. Evaluate your standing now.</p>
                        <a href="{{ route('student.exam_demo') }}"
                            class="inline-block mt-6 bg-white text-blue-700 font-semibold py-2.5 px-6 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                            Start Free Mock Test
                        </a>
                    </div>
                    <!-- Decorative Icon -->
                    <div class="hidden mr-10 transform scale-150 md:block opacity-20">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z">
                            </path>
                        </svg>
                    </div>
                </div>
                <!-- Circles -->
                <div class="absolute top-0 right-0 w-64 h-64 -mt-16 -mr-16 bg-white rounded-full opacity-5"></div>
                <div class="absolute bottom-0 left-0 w-40 h-40 -mb-16 -ml-16 bg-white rounded-full opacity-5"></div>
            </div>

            <!-- Recommended Tests -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Recommended for You</h3>
                    <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All Tests
                        &rarr;</a>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <!-- Test Card 1 -->
                    <div
                        class="p-5 transition-all bg-white border border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-md">
                        <div class="flex items-start justify-between mb-4">
                            <span
                                class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded uppercase tracking-wide">SSC
                                CGL</span>
                            <span class="px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded">120 Mins</span>
                        </div>
                        <h4 class="mb-2 text-lg font-bold text-gray-800">General Awareness Mock - Tier I</h4>
                        <div class="flex items-center mb-5 space-x-4 text-sm text-gray-500">
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg> 50 Qns</span>
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg> 100 Marks</span>
                        </div>
                        <button
                            class="w-full py-2.5 border border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-600 hover:text-white transition-colors">
                            Attempt Now
                        </button>
                    </div>

                    <!-- Test Card 2 -->
                    <div
                        class="p-5 transition-all bg-white border border-gray-200 rounded-xl hover:border-purple-300 hover:shadow-md">
                        <div class="flex items-start justify-between mb-4">
                            <span
                                class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2.5 py-1 rounded uppercase tracking-wide">Banking</span>
                            <span class="px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded">60 Mins</span>
                        </div>
                        <h4 class="mb-2 text-lg font-bold text-gray-800">SBI PO Prelims - Full Test 3</h4>
                        <div class="flex items-center mb-5 space-x-4 text-sm text-gray-500">
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg> 100 Qns</span>
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg> 100 Marks</span>
                        </div>
                        <button
                            class="w-full py-2.5 border border-purple-600 text-purple-600 font-semibold rounded-lg hover:bg-purple-600 hover:text-white transition-colors">
                            Attempt Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Subject Analysis -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Subject-wise Analysis</h3>
                </div>
                <div class="p-6 bg-white border border-gray-200 rounded-xl">
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1 text-sm font-medium">
                                <span class="text-gray-700">Quantitative Aptitude</span>
                                <span class="text-gray-900">75%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-indigo-600 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1 text-sm font-medium">
                                <span class="text-gray-700">Reasoning Ability</span>
                                <span class="text-gray-900">85%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-indigo-600 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1 text-sm font-medium">
                                <span class="text-gray-700">English Language</span>
                                <span class="text-gray-900">60%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-indigo-600 rounded-full" style="width: 60%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1 text-sm font-medium">
                                <span class="text-gray-700">General Awareness</span>
                                <span class="text-gray-900">45%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-indigo-600 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: History & Analytics -->
        <div class="w-full space-y-6 lg:w-1/4">

            <!-- Mini Analytics -->
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Weekly Performance</h3>
                <div class="flex items-end justify-between h-32 px-1">
                    <!-- Day 1 -->
                    <div class="flex flex-col items-center w-1/5 gap-1 group">
                        <div
                            class="relative w-full h-12 transition-colors bg-blue-100 rounded-t-sm group-hover:bg-blue-200">
                        </div>
                        <span class="text-[10px] text-gray-400">M</span>
                    </div>
                    <!-- Day 2 -->
                    <div class="flex flex-col items-center w-1/5 gap-1 group">
                        <div
                            class="relative w-full h-20 transition-colors bg-blue-300 rounded-t-sm group-hover:bg-blue-400">
                        </div>
                        <span class="text-[10px] text-gray-400">T</span>
                    </div>
                    <!-- Day 3 -->
                    <div class="flex flex-col items-center w-1/5 gap-1 group">
                        <div
                            class="relative w-full h-10 transition-colors bg-blue-500 rounded-t-sm group-hover:bg-blue-600">
                        </div>
                        <span class="text-[10px] text-gray-400">W</span>
                    </div>
                    <!-- Day 4 -->
                    <div class="flex flex-col items-center w-1/5 gap-1 group">
                        <div
                            class="relative w-full h-16 transition-colors bg-blue-200 rounded-t-sm group-hover:bg-blue-300">
                        </div>
                        <span class="text-[10px] text-gray-400">T</span>
                    </div>
                    <!-- Day 5 -->
                    <div class="flex flex-col items-center w-1/5 gap-1 group">
                        <div
                            class="relative w-full h-24 transition-colors bg-blue-600 rounded-t-sm group-hover:bg-blue-700">
                        </div>
                        <span class="text-[10px] text-gray-400">F</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Timeline -->
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Activity Log</h3>
                <div class="space-y-6 relative border-l border-gray-100 ml-1.5">

                    <div class="relative pl-6">
                        <div class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full bg-blue-500 ring-4 ring-white"></div>
                        <p class="text-sm font-semibold text-gray-800">Attempted SBI PO Mock</p>
                        <p class="text-xs text-gray-500 mt-0.5">Today, 10:30 AM</p>
                        <span class="inline-block mt-2 text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded">Score:
                            72/100</span>
                    </div>

                    <div class="relative pl-6">
                        <div class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full bg-gray-300 ring-4 ring-white"></div>
                        <p class="text-sm font-semibold text-gray-800">Plan Expiring Soon</p>
                        <p class="text-xs text-gray-500 mt-0.5">Yesterday</p>
                    </div>

                    <div class="relative pl-6">
                        <div class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full bg-gray-300 ring-4 ring-white"></div>
                        <p class="text-sm font-semibold text-gray-800">Profile Updated</p>
                        <p class="text-xs text-gray-500 mt-0.5">2 days ago</p>
                    </div>
                </div>
            </div>

            <!-- Learning Materials -->
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Study Material</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="flex items-start group">
                            <div
                                class="flex-shrink-0 p-2 text-red-500 transition-colors rounded-lg bg-red-50 group-hover:bg-red-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-800 transition-colors group-hover:text-blue-600">
                                    Monthly Current Affairs PDF</p>
                                <p class="text-xs text-gray-500">Dec 2025 • 2.5 MB</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-start group">
                            <div
                                class="flex-shrink-0 p-2 text-purple-500 transition-colors rounded-lg bg-purple-50 group-hover:bg-purple-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-800 transition-colors group-hover:text-blue-600">
                                    Geometry Tricks Video</p>
                                <p class="text-xs text-gray-500">35 Mins • Math</p>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

    </div>
@endsection
