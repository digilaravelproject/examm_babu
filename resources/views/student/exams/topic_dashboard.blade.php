@extends('layouts.student')

@section('content')

{{-- Style Block --}}
<style>
    .text-brand-blue { color: var(--brand-blue); }
    .bg-brand-blue { background-color: var(--brand-blue); }
    .btn-brand-primary { background-color: var(--brand-blue); color: white; }
    .btn-brand-primary:hover { background-color: #055c93; box-shadow: 0 4px 12px rgba(7, 119, 190, 0.3); }
    .btn-brand-action { background-color: var(--brand-green); color: white; }
    .btn-brand-action:hover { background-color: #7ab326; box-shadow: 0 4px 12px rgba(148, 201, 64, 0.4); }

    /* Animation classes for Smooth Toggle */
    .exam-grid-container {
        display: none; /* By default hidden */
        transition: all 0.3s ease-in-out;
    }
    .topic-arrow {
        transition: transform 0.3s ease;
    }
    .topic-header.active .topic-arrow {
        transform: rotate(180deg);
    }
    .topic-header.active {
        background-color: #f8fafc; /* Light gray active state */
        border-radius: 0.75rem;
    }
</style>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8 bg-slate-50">

    {{-- Dashboard Hero --}}
    <div class="flex flex-col justify-between pb-6 mb-10 border-b md:flex-row md:items-center border-slate-200">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Topic-wise Exams</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Click on a topic below to view available tests.</p>
        </div>
        <a href="{{ route('student.exams.live') }}"
           class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-bold text-white transition-all rounded-lg shadow-sm md:mt-0 btn-brand-primary">
            <span>View All Live Tests</span>
        </a>
    </div>

    {{-- ORGANIZED SECTIONS BY TOPIC --}}
    @if(count($organizedExams) > 0)

        <div class="space-y-4"> {{-- Container for the list --}}
            @foreach($organizedExams as $index => $section)
                <div class="bg-white border shadow-sm border-slate-200 rounded-xl">

                    {{-- ✨ CLICKABLE TOPIC HEADER --}}
                    <div class="flex items-center justify-between p-5 cursor-pointer topic-header select-none hover:bg-slate-50 rounded-xl"
                         onclick="toggleTopic('topic-content-{{ $index }}', this)">

                        <div class="flex items-center gap-4">
                            {{-- Topic Icon --}}
                            <div class="flex items-center justify-center w-12 h-12 bg-white border shadow-sm rounded-xl border-slate-200" style="color: var(--brand-pink);">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            {{-- Text Content --}}
                            <div>
                                <h3 class="text-lg font-bold leading-tight text-slate-900">
                                    {{ $section['topic_name'] }}
                                </h3>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-xs font-medium text-slate-500">
                                        Category: <span class="font-bold text-slate-700">{{ $section['category_name'] }}</span>
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ count($section['schedules']) }} Tests
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Arrow Icon --}}
                        <div class="text-slate-400 topic-arrow">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- ✨ EXPANDABLE EXAM GRID (HIDDEN BY DEFAULT) --}}
                    <div id="topic-content-{{ $index }}" class="exam-grid-container bg-slate-50/50">
                        <div class="p-5 border-t border-slate-100">

                            {{-- Horizontal Scrollable Grid for this Topic --}}
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                @foreach($section['schedules'] as $schedule)

                                    {{-- TIMEZONE LOGIC (Kept exactly same) --}}
                                    @php
                                        $adminTimezone = 'Asia/Kolkata';
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
                                    @endphp

                                    {{-- Exam Card --}}
                                    <div class="relative flex flex-col h-full transition-all duration-300 bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-md">

                                        <div class="flex-1 p-4">
                                            <div class="flex items-start justify-between mb-3">
                                                <span class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 rounded border border-slate-200">
                                                    {{ $schedule->exam->examType->name ?? 'Test' }}
                                                </span>

                                                {{-- Status Badge Logic --}}
                                                @if($isLive)
                                                    <span class="flex items-center gap-1.5 px-2 py-1 bg-red-50 text-red-600 rounded-full border border-red-100">
                                                        <span class="relative flex w-1.5 h-1.5">
                                                            <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                                                            <span class="relative inline-flex w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                        </span>
                                                        <span class="text-[10px] font-bold uppercase tracking-wider">Live</span>
                                                    </span>
                                                @elseif($isUpcoming)
                                                    <span class="px-2 py-1 text-[10px] font-bold rounded-full border border-orange-100 uppercase tracking-wider" style="background-color: #fff7ed; color: #ea580c;">Upcoming</span>
                                                @else
                                                    <span class="px-2 py-1 text-[10px] font-bold text-slate-400 bg-slate-50 rounded-full border border-slate-100 uppercase tracking-wider">Ended</span>
                                                @endif
                                            </div>

                                            <h4 class="mb-2 text-sm font-bold leading-tight transition-colors text-slate-900 line-clamp-2"
                                                title="{{ $schedule->exam->title }}">
                                                {{ $schedule->exam->title }}
                                            </h4>

                                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                                 <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                 {{ $startDt->format('d M Y, h:i A') }}
                                            </div>
                                        </div>

                                        {{-- Card Footer --}}
                                        <div class="p-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                                            @php
                                                $attemptsTaken = $attemptCounts[$schedule->id] ?? 0;
                                                $settings = $schedule->exam->settings;
                                                $maxAttempts = $settings['no_of_attempts'] ?? 0;
                                                $isLimitReached = ($maxAttempts > 0 && $attemptsTaken >= $maxAttempts);
                                            @endphp

                                            @if(!$schedule->exam->is_paid || in_array($schedule->exam->micro_category_id, $subscribedCategoryIds))
                                                @if($isLimitReached)
                                                    <button disabled class="w-full py-2 text-[10px] font-bold text-red-600 bg-red-50 rounded border border-red-100 cursor-not-allowed opacity-80">
                                                        Attempts Full ({{ $attemptsTaken }}/{{ $maxAttempts }})
                                                    </button>
                                                @elseif($isUpcoming)
                                                     <button disabled class="w-full py-2 text-[10px] font-bold text-slate-400 bg-slate-100 rounded border border-slate-200 cursor-not-allowed">
                                                        Please Wait
                                                     </button>
                                                @elseif($isExpired)
                                                     <button disabled class="w-full py-2 text-[10px] font-bold text-slate-400 bg-slate-100 rounded border border-slate-200 cursor-not-allowed">
                                                        Closed
                                                     </button>
                                                @else
                                                    <a href="{{ route('student.exam.start', $schedule->id) }}"
                                                       class="w-full inline-flex justify-center items-center py-2 text-[10px] font-bold rounded transition-colors shadow-sm btn-brand-action">
                                                        {{ $attemptsTaken > 0 ? 'Retake Exam' : 'Attempt Now' }}
                                                    </a>
                                                @endif
                                            @else
                                                 <a href="#" class="w-full inline-flex justify-center items-center py-2 text-[10px] font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded">
                                                    Unlock
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-16 text-center bg-white border-2 border-dashed rounded-2xl border-slate-300">
            <h3 class="text-lg font-bold text-slate-900">No topic-wise exams found</h3>
            <p class="max-w-xs mx-auto mt-2 text-sm text-slate-500">There are currently no exams assigned to specific topics in your subscription.</p>
        </div>
    @endif

</div>

{{-- Simple Script to Toggle visibility --}}
<script>
    function toggleTopic(contentId, headerElement) {
        // 1. Get the content div
        const contentDiv = document.getElementById(contentId);

        // 2. Toggle the visual state
        if (contentDiv.style.display === "block") {
            contentDiv.style.display = "none";
            headerElement.classList.remove('active');
        } else {
            // Optional: Close others before opening this one (Accordion style)
            document.querySelectorAll('.exam-grid-container').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.topic-header').forEach(el => el.classList.remove('active'));

            contentDiv.style.display = "block";
            headerElement.classList.add('active');
        }
    }
</script>

@endsection
