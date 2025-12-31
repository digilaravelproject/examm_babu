@extends('layouts.student')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 mb-8 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Exam Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Your scheduled exams and practice sets.</p>
        </div>
        <a href="{{ route('student.exams.live') }}" class="text-sm font-bold text-[var(--brand-blue)] hover:underline">View All Live Exams &rarr;</a>
    </div>

    {{-- Upcoming Schedule Grid --}}
    <h3 class="mb-4 text-lg font-bold text-slate-800">Scheduled for You</h3>

    @if($examSchedules->count() > 0)
        <div class="grid grid-cols-1 gap-6 mb-12 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($examSchedules as $schedule)
                <div class="relative p-5 overflow-hidden transition-all duration-300 bg-white border group rounded-2xl border-slate-200 hover:shadow-lg">

                    {{-- Status Badge --}}
                    <div class="absolute top-0 right-0">
                        <span class="bg-red-50 text-red-600 text-[10px] font-bold px-3 py-1 rounded-bl-lg">LIVE</span>
                    </div>

                    <h4 class="mb-1 font-bold transition-colors text-slate-900 group-hover:text-blue-600">{{ $schedule->exam->title }}</h4>
                    <p class="mb-4 text-xs text-slate-500">{{ $schedule->exam->subCategory->name }}</p>

                    <div class="flex items-center gap-2 mb-4 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>{{ \Carbon\Carbon::parse($schedule->start_date)->format('d M, h:i A') }}</span>
                    </div>

                    @if($subscription)
                        <a href="#" class="block w-full py-2.5 text-center rounded-xl bg-[var(--brand-blue)] text-white text-sm font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">Start Exam</a>
                    @else
                        <a href="{{ route('pricing') }}" class="block w-full py-2.5 text-center rounded-xl border-2 border-[var(--brand-blue)] text-[var(--brand-blue)] text-sm font-bold hover:bg-blue-50 transition-all">Unlock Now</a>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="py-10 mb-12 text-center border border-dashed bg-slate-50 rounded-2xl border-slate-300">
            <p class="text-sm text-slate-500">No exams scheduled for today.</p>
        </div>
    @endif

    {{-- Exam Types / Categories --}}
    <h3 class="mb-4 text-lg font-bold text-slate-800">Explore by Category</h3>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-5">
        @foreach($examTypes as $type)
            <a href="{{ route('student.exams.type', $type->slug) }}" class="flex flex-col items-center justify-center p-6 transition-all bg-white border border-slate-200 rounded-2xl hover:border-blue-300 hover:shadow-md group">
                {{-- Placeholder Icon Logic --}}
                <div class="flex items-center justify-center w-12 h-12 mb-3 text-2xl transition-transform rounded-full bg-blue-50 group-hover:scale-110">
                    📝
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-blue-600">{{ $type->name }}</span>
            </a>
        @endforeach
    </div>

</div>
@endsection
