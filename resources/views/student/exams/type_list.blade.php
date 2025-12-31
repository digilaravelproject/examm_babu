@extends('layouts.student')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">

    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('student.exams.dashboard') }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $type->name }}</h1>
            <p class="text-sm text-slate-500">Practice with our curated {{ strtolower($type->name) }} collection.</p>
        </div>
    </div>

    @if($exams->count() > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($exams as $exam)
                <div class="p-6 transition-all duration-300 bg-white border shadow-sm rounded-2xl border-slate-200 hover:shadow-lg group">

                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-[var(--brand-blue)] flex items-center justify-center font-bold text-xl">
                            {{ substr($exam->title, 0, 1) }}
                        </div>
                        @if($exam->is_paid && !$subscription)
                            <span class="px-2 py-1 text-xs font-bold rounded text-amber-600 bg-amber-50">PREMIUM</span>
                        @else
                            <span class="px-2 py-1 text-xs font-bold text-green-600 rounded bg-green-50">FREE</span>
                        @endif
                    </div>

                    <h3 class="mb-1 text-lg font-bold transition-colors text-slate-900 group-hover:text-blue-600">{{ $exam->title }}</h3>
                    <p class="mb-6 text-xs text-slate-500">{{ $exam->subCategory->name }}</p>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="text-xs font-medium text-slate-500">
                            {{ $exam->duration }} Minutes
                        </div>

                        @if(!$exam->is_paid || $subscription)
                            <a href="#" class="text-sm font-bold text-[var(--brand-blue)] hover:underline">Start Now &rarr;</a>
                        @else
                            <a href="{{ route('pricing') }}" class="flex items-center gap-1 text-xs font-bold text-slate-400 hover:text-slate-600">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Unlock
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $exams->links() }}
        </div>
    @else
        <div class="py-20 text-center border border-dashed bg-slate-50 rounded-2xl border-slate-300">
            <p class="text-slate-500">No exams found in this category.</p>
        </div>
    @endif
</div>
@endsection
