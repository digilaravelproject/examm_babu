@foreach($schedules as $schedule)
    <div class="flex flex-col h-full p-5 transition-all bg-white border shadow-sm rounded-2xl border-slate-200 hover:shadow-md">

        <div class="flex items-start justify-between mb-3">
            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Active</span>
            <span class="text-xs font-semibold text-slate-400">{{ $schedule->exam->examType->name ?? 'Exam' }}</span>
        </div>

        <h3 class="mb-1 text-lg font-bold text-slate-900">{{ $schedule->exam->title }}</h3>
        <p class="mb-4 text-sm text-slate-500">{{ $schedule->exam->subCategory->name }}</p>

        <div class="pt-4 mt-auto border-t border-slate-100">
            <div class="flex items-center justify-between mb-4 text-xs text-slate-500">
                <span>Ends: {{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, h:i A') }}</span>
                <span>{{ $schedule->exam->duration }} Mins</span>
            </div>

            @if($subscription)
                <button class="w-full py-2.5 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors shadow-md shadow-green-200">Attempt Now</button>
            @else
                <a href="{{ route('pricing') }}" class="flex items-center justify-center w-full py-2.5 rounded-lg bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-colors gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    Unlock
                </a>
            @endif
        </div>
    </div>
@endforeach
