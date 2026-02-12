@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('content')

@php
    // --- Dynamic Route Logic ---
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';

    // Prepare Parameters
    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }

    // Pre-generate URL
    $urlBack = route($routePrefix . 'exams.schedules.index', array_merge($routeParams, ['exam' => $exam->id]));
@endphp

    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Back Button --}}
        <div class="mb-6">
            {{-- FIX: Dynamic Back URL --}}
            <a href="{{ $urlBack }}"
                class="flex items-center gap-2 text-gray-500 hover:text-gray-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Schedules
            </a>
        </div>

        <div class="space-y-6">
            {{-- Header Section --}}
            <div
                class="flex flex-col justify-between gap-6 p-6 bg-white border border-gray-200 shadow-sm md:flex-row md:items-center rounded-xl">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                        {{ $exam->title }} - Schedule Report
                    </h1>
                    <p class="mt-1 text-sm font-medium text-gray-500">
                        Schedule ID: <span
                            class="px-2 py-1 font-mono text-gray-700 bg-gray-100 rounded">{{ $schedule->code ?? $schedule->id }}</span>
                    </p>
                </div>
                {{-- Basic Blue Button --}}
                <button
                    class="px-6 py-2.5 text-sm font-bold text-white uppercase transition-colors bg-blue-600 rounded hover:bg-blue-700">
                    Detailed Report
                </button>
            </div>

            {{-- Stats Grid --}}
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                <div
                    class="grid grid-cols-1 divide-y divide-gray-200 md:grid-cols-2 lg:grid-cols-4 md:divide-y-0 md:divide-x">

                    {{-- Row 1 --}}
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Total Attempts</p>
                        {{-- Standard Blue Text for Numbers --}}
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['total_attempts'] }}</p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Pass Attempts</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['pass_attempts'] }}</p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Fail Attempts</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['fail_attempts'] }}</p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Unique Test Takers</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['unique_takers'] }}</p>
                    </div>

                    {{-- Row 2 (Separator for mobile/desktop logic) --}}
                    <div class="hidden h-px bg-gray-200 col-span-full md:block"></div>

                    {{-- Row 2 Data --}}
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Avg. Time Spent</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['avg_time'] }} <span
                                class="text-lg text-gray-400">Min</span></p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Avg. Score</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">
                            {{ $stats['avg_score'] }}<span
                                class="text-lg text-gray-400">/{{ $exam->total_marks ?? 'N/A' }}</span>
                        </p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">High Score</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['high_score'] }}</p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Low Score</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['low_score'] }}</p>
                    </div>

                    {{-- Row 3 (Separator) --}}
                    <div class="hidden h-px bg-gray-200 col-span-full md:block"></div>

                    {{-- Row 3 Data --}}
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Avg. Percentage</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['avg_percentage'] }}%</p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Avg. Accuracy</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['avg_accuracy'] }}%</p>
                    </div>
                    <div class="p-8 text-center transition-colors border-b border-gray-200 md:border-b-0 hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Avg. Speed</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['avg_speed'] }} <span
                                class="text-lg text-gray-400">que/hr</span></p>
                    </div>
                    <div class="p-8 text-center transition-colors hover:bg-gray-50">
                        <p class="text-sm font-medium text-gray-600">Avg. Questions Answered</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['avg_answered'] }}</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
