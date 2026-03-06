@extends('layouts.student')

@section('content')

{{-- Define Styles locally for this page (or move to your main CSS) --}}
<style>
    :root {
        --brand-blue: #0777be;
        --brand-pink: #f062a4;
        --brand-green: #94c940;
        --brand-sky: #7fd2ea;
        --sidebar-bg: #0f172a;
    }

    .text-brand-blue { color: var(--brand-blue); }
    .bg-brand-blue { background-color: var(--brand-blue); }
    .hover-bg-brand-blue:hover { background-color: #0666a3; } /* Slightly darker for hover */

    .text-brand-pink { color: var(--brand-pink); }
    .bg-brand-pink { background-color: var(--brand-pink); }

    .text-brand-green { color: var(--brand-green); }
    .bg-brand-green { background-color: var(--brand-green); }

    /* Button Gradients / Solid Colors */
    .btn-brand-primary {
        background-color: var(--brand-blue);
        color: white;
    }
    .btn-brand-primary:hover {
        background-color: #055c93; /* Darker Blue */
        box-shadow: 0 4px 12px rgba(7, 119, 190, 0.3);
    }

    .btn-brand-action {
        background-color: var(--brand-green);
        color: white;
    }
    .btn-brand-action:hover {
        background-color: #7ab326; /* Darker Green */
        box-shadow: 0 4px 12px rgba(148, 201, 64, 0.4);
    }
</style>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8 bg-slate-50">

    {{-- Dashboard Hero --}}
    <div class="flex flex-col justify-between pb-6 mb-10 border-b md:flex-row md:items-center border-slate-200">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">My Learning Dashboard</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Access your scheduled tests and subscribed plans.</p>
        </div>
        <a href="{{ route('student.exams.live') }}"
           class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-bold text-white transition-all rounded-lg shadow-sm md:mt-0 btn-brand-primary">
            <span>View Live Tests</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
        </a>
    </div>

    {{-- ORGANIZED SECTIONS BY PLAN --}}
    @if(count($organizedExams) > 0)

        @php $isFirstSection = true; @endphp
        @foreach($organizedExams as $section)
            @php
                $isHighlighted = session('highlight_plan_id') == $section['plan_id'];
            @endphp

            @if($isHighlighted)
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-green-600 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Active Plan (Highlighted)
                    </h2>
                </div>
            @elseif(session('highlight_plan_id') && !$isFirstSection && $loop->index == 1)
                <div class="mb-6 mt-12 pb-2 border-b-2 border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800">Other Plans</h2>
                </div>
            @endif

            <div class="mb-12 {{ $isHighlighted ? 'p-6 bg-green-50/50 border-2 border-green-500 rounded-2xl shadow-sm' : '' }}">
                {{-- ✨ Enhanced Section Header --}}
                <div class="flex items-start gap-4 mb-6">
                    {{-- Icon Box --}}
                    <div class="flex items-center justify-center w-12 h-12 bg-white border shadow-sm rounded-xl {{ $isHighlighted ? 'border-green-200 text-green-600' : 'border-slate-200' }}" style="{{ !$isHighlighted ? 'color: var(--brand-blue);' : '' }}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>

                    {{-- Text Content --}}
                    <div>
                        <h3 class="text-xl font-bold leading-tight text-slate-900 flex items-center">
                            {{ $section['plan_name'] }}
                            @if($isHighlighted)
                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    Status: Active
                                </span>
                            @endif
                        </h3>
                        <div class="mt-1.5 flex items-center">
                            {{-- Category Badge --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide border"
                                  style="background-color: rgba(127, 210, 234, 0.15); color: var(--brand-blue); border-color: rgba(127, 210, 234, 0.3);">
                                {{ $section['category_name'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Group Exams by Subject --}}
                @php
                    $groupedExams = $section['exams']->groupBy(function($exam) {
                        return $exam->subCategory->name ?? 'Uncategorized';
                    });
                @endphp

                @foreach($groupedExams as $subjectName => $subjectExams)
                    <div class="mb-8 last:mb-0">
                        <h4 class="mb-4 text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[var(--brand-blue)]"></span>
                            {{ $subjectName }}
                        </h4>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($subjectExams as $exam)

                        {{-- 🟢 TIMEZONE FIXED LOGIC --}}
                        @php
                            $schedule = $exam->schedules->first();
                            $adminTimezone = 'Asia/Kolkata';

                            $isDeactivated = !$schedule;
                            $isUpcoming = false;
                            $isExpired = false;
                            $isLive = false;

                            if ($schedule) {
                                $startDateStr = \Carbon\Carbon::parse($schedule->start_date)->format('Y-m-d');
                                $startDt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $startDateStr . ' ' . $schedule->start_time, $adminTimezone);

                                if($schedule->end_date) {
                                    $endDateStr = \Carbon\Carbon::parse($schedule->end_date)->format('Y-m-d');
                                    $endTimeStr = $schedule->end_time ?? '23:59:59';
                                    $endDt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $endDateStr . ' ' . $endTimeStr, $adminTimezone);
                                } else {
                                    if($schedule->schedule_type == 'fixed') {
                                        $endDt = $startDt->copy()->addMinutes($schedule->grace_period ?? 30);
                                    } else {
                                        $endDt = $startDt->copy()->addYears(1);
                                    }
                                }

                                $now = now()->setTimezone($adminTimezone);
                                $isUpcoming = $now->lt($startDt);
                                $isExpired = $now->gt($endDt);
                                $isLive = !$isUpcoming && !$isExpired;
                            }
                        @endphp

                        {{-- ✨ Enhanced Card --}}
                        <div class="relative flex flex-col h-full transition-all duration-300 bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-lg group hover:-translate-y-1">

                            {{-- Card Header --}}
                            <div class="flex-1 p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded border border-slate-200">
                                            {{ $exam->subCategory->name ?? 'Subject' }}
                                        </span>
                                        @if($exam->topic)
                                        <span class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded border border-slate-200">
                                            {{ $exam->topic->name }}
                                        </span>
                                        @endif
                                    </div>

                                    {{-- Status Badge --}}
                                    @if($isDeactivated)
                                        <span class="px-2 py-1 text-[10px] font-bold text-slate-500 bg-slate-200 rounded-full border border-slate-300 uppercase tracking-wider">
                                            Deactivated
                                        </span>
                                    @elseif($isLive)
                                        <span class="flex items-center gap-1.5 px-2 py-1 bg-red-50 text-red-600 rounded-full border border-red-100">
                                            <span class="relative flex w-2 h-2">
                                              <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                                              <span class="relative inline-flex w-2 h-2 bg-red-500 rounded-full"></span>
                                            </span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider">Live</span>
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="px-2 py-1 text-[10px] font-bold rounded-full border border-orange-100 uppercase tracking-wider"
                                              style="background-color: #fff7ed; color: #ea580c;">
                                            Upcoming
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-[10px] font-bold text-slate-400 bg-slate-50 rounded-full border border-slate-100 uppercase tracking-wider">
                                            Ended
                                        </span>
                                    @endif
                                </div>

                                <h4 class="mb-2 text-base font-bold leading-tight transition-colors text-slate-900 line-clamp-2"
                                    style="transition: color 0.3s;"
                                    onmouseover="this.style.color='var(--brand-blue)'"
                                    onmouseout="this.style.color=''"
                                    title="{{ $exam->title }}">
                                    {{ $exam->title }}
                                </h4>

                                {{-- Time Details --}}
                                @if($schedule)
                                <div class="flex items-center gap-2 mt-3 text-xs font-medium text-slate-500">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span>{{ $startDt->format('d M Y') }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span>{{ $startDt->format('h:i A') }}</span>
                                </div>
                                @endif
                            </div>

                            {{-- Card Footer / Action --}}
<div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
    @if($isDeactivated)
        <button disabled class="w-full py-2.5 text-xs font-bold text-slate-500 bg-slate-200 rounded-lg border border-slate-300 cursor-not-allowed">
            Status: Deactivated
        </button>
    @else
        @php
            // 1. Get Attempts Taken
            $attemptsTaken = $attemptCounts[$schedule->id] ?? 0;

            // 2. Get Max Attempts from Settings (Default to 0/Unlimited if null)
            $settings = $exam->settings;
            $maxAttempts = $settings['no_of_attempts'] ?? 0;

            // 3. Logic: Lock only if Limit is set (>0) AND Taken >= Limit
            $isLimitReached = ($maxAttempts > 0 && $attemptsTaken >= $maxAttempts);
        @endphp

        @if(!$exam->is_paid || in_array($exam->micro_category_id, $subscribedCategoryIds))

            @if($isLimitReached)
                {{-- 🛑 CONDITION 1: LIMIT REACHED --}}
                <button disabled class="w-full py-2.5 text-xs font-bold text-red-600 bg-red-50 rounded-lg border border-red-100 cursor-not-allowed flex items-center justify-center gap-2 opacity-80">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Max Attempts Reached ({{ $attemptsTaken }}/{{ $maxAttempts }})
                </button>

            @elseif($isUpcoming)
                 {{-- ⏳ CONDITION 2: UPCOMING --}}
                 <button disabled class="w-full py-2.5 text-xs font-bold text-slate-400 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Starts {{ $startDt->diffForHumans() }}
                </button>

            @elseif($isExpired)
                {{-- ❌ CONDITION 3: EXPIRED --}}
                 <button disabled class="w-full py-2.5 text-xs font-bold text-slate-400 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed">
                    Test Closed
                </button>

            @else
                {{-- 🚀 CONDITION 4: LIVE / READY TO START --}}
                <a href="{{ route('student.exam.start', $schedule->id) }}"
                   class="w-full inline-flex justify-center items-center py-2.5 text-xs font-bold rounded-lg transition-colors shadow-sm btn-brand-action">
                    @if($attemptsTaken > 0)
                        Retake Exam ({{ $attemptsTaken }} Done) &rarr;
                    @else
                        Attempt Now &rarr;
                    @endif
                </a>
            @endif

        @else
            {{-- 🔒 LOCKED --}}
             <a href="#" class="w-full inline-flex justify-center items-center py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
                Unlock Test
            </a>
        @endif
    @endif
</div>
                        </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-16 text-center bg-white border-2 border-dashed rounded-2xl border-slate-300">
            <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-slate-50">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900">No active plans found</h3>
            <p class="max-w-xs mx-auto mt-2 mb-6 text-sm text-slate-500">You haven't subscribed to any test series yet. Explore our plans to get started.</p>
            <a href="{{ route('pricing') }}"
               class="px-6 py-2.5 rounded-xl transition-all shadow-lg btn-brand-primary font-bold">
                Browse Plans
            </a>
        </div>
    @endif

    {{-- Browse More Categories (Commented Out) --}}
    {{-- <div class="pt-10 mt-10 border-t border-slate-200">
        <h3 class="mb-5 text-sm font-bold tracking-wide uppercase text-slate-500">Explore Categories</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6">
            @foreach($examTypes as $type)
                <a href="{{ route('student.exams.type', $type->slug) }}" class="flex flex-col items-center justify-center p-4 text-center transition-all bg-white border border-slate-200 rounded-xl hover:shadow-md group"
                   style="border-color: transparent;"
                   onmouseover="this.style.borderColor='var(--brand-sky)'"
                   onmouseout="this.style.borderColor='transparent'">

                    <div class="flex items-center justify-center w-10 h-10 mb-3 text-lg font-bold transition-colors rounded-lg"
                         style="background-color: var(--brand-sky); color: var(--sidebar-bg);">
                        {{ substr($type->name, 0, 1) }}
                    </div>
                    <span class="text-xs font-bold transition-colors text-slate-700 line-clamp-1"
                          onmouseover="this.style.color='var(--brand-blue)'"
                          onmouseout="this.style.color=''">
                        {{ $type->name }}
                    </span>
                </a>
            @endforeach
        </div>
    </div> --}}

</div>
@endsection
