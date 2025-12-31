@extends('layouts.student')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">

    {{-- Hero --}}
    <div class="flex items-center justify-between pb-4 mb-8 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-800">My Learning Dashboard</h1>
            <p class="text-xs font-medium text-slate-500">Access tests from your subscribed plans.</p>
        </div>
        <a href="{{ route('student.exams.live') }}" class="text-xs font-bold text-blue-600 hover:underline">See All Live Tests &rarr;</a>
    </div>

    {{-- ORGANIZED SECTIONS BY PLAN --}}
    @if(count($organizedExams) > 0)

        @foreach($organizedExams as $section)
            <div class="mb-10">
                {{-- Section Header --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-[var(--brand-blue)] rounded-full"></div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $section['plan_name'] }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $section['category_name'] }}</p>
                    </div>
                </div>

                {{-- Horizontal Scrollable Grid for this Plan --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($section['schedules'] as $schedule)
                        <div class="relative flex flex-col h-full p-4 transition-all bg-white border rounded-lg border-slate-200 hover:border-blue-300 hover:shadow-md group">

                            {{-- Live Dot --}}
                            @if(\Carbon\Carbon::parse($schedule->start_date)->isToday())
                                <span class="absolute top-3 right-3 flex h-2.5 w-2.5">
                                    <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                </span>
                            @endif

                            <div class="flex-1 pr-4 mb-3">
                                <h4 class="text-sm font-bold text-slate-900 leading-tight mb-1 group-hover:text-[var(--brand-blue)] line-clamp-2" title="{{ $schedule->exam->title }}">
                                    {{ $schedule->exam->title }}
                                </h4>
                                <p class="text-[10px] font-semibold text-slate-500 bg-slate-50 inline-block px-1.5 py-0.5 rounded border border-slate-100">
                                    {{ $schedule->exam->examType->name ?? 'Test' }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-100">
                                <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, h:i A') }}</span>
                                </div>

                                {{-- Unlock Logic: Check Array --}}
                                @if(!$schedule->exam->is_paid || in_array($schedule->exam->sub_category_id, $subscribedCategoryIds))
                                    <a href="#" class="text-[10px] font-bold text-white bg-slate-900 px-3 py-1.5 rounded hover:bg-[var(--brand-blue)] transition-colors shadow-sm">
                                        Start
                                    </a>
                                @else
                                    <a href="{{ route('pricing') }}" class="text-[10px] font-bold text-slate-500 border border-slate-200 px-2 py-1 rounded hover:bg-slate-50 transition-colors">
                                        Unlock
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

    @else
        <div class="py-12 mb-8 text-center border-2 border-dashed rounded-xl bg-slate-50 border-slate-300">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            <h3 class="text-sm font-bold text-slate-800">No active plans found</h3>
            <p class="mt-1 mb-4 text-xs text-slate-500">Subscribe to a plan to see exams here.</p>
            <a href="{{ route('pricing') }}" class="text-xs font-bold text-white bg-[var(--brand-blue)] px-4 py-2 rounded-lg hover:bg-blue-700">Browse Plans</a>
        </div>
    @endif

    {{-- Browse More Categories --}}
    <div class="pt-8 mt-12 border-t border-slate-200">
        <h3 class="mb-4 text-sm font-bold tracking-wide uppercase text-slate-700">Practice Categories</h3>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6">
            @foreach($examTypes as $type)
                <a href="{{ route('student.exams.type', $type->slug) }}" class="flex items-center gap-3 p-3 transition-all bg-white border rounded-lg border-slate-200 hover:border-blue-400 hover:shadow-sm group">
                    <div class="w-8 h-8 rounded bg-blue-50 text-[var(--brand-blue)] flex items-center justify-center text-sm font-bold group-hover:bg-[var(--brand-blue)] group-hover:text-white transition-colors">
                        {{ substr($type->name, 0, 1) }}
                    </div>
                    <span class="text-xs font-bold truncate text-slate-700 group-hover:text-blue-700">{{ $type->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
