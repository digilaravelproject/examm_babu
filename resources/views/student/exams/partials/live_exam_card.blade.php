@foreach($schedules as $schedule)
    <div class="relative flex flex-col justify-between h-full p-4 overflow-hidden transition-all bg-white border rounded-lg group border-slate-200 hover:border-blue-300 hover:shadow-sm">

        <div class="absolute top-0 right-0 p-3">
            <span class="relative flex w-2 h-2">
              <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
              <span class="relative inline-flex w-2 h-2 bg-red-500 rounded-full"></span>
            </span>
        </div>

        <div>
            <div class="flex flex-col pr-4 mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    {{ $schedule->exam->examType->name ?? 'Exam' }}
                </span>
                <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-[var(--brand-blue)] line-clamp-2 transition-colors">
                    {{ $schedule->exam->title }}
                </h3>
            </div>
            <p class="text-[11px] font-semibold text-slate-500 bg-slate-50 inline-block px-2 py-0.5 rounded border border-slate-100">
                {{ $schedule->exam->subCategory->name }}
            </p>
        </div>

        <div class="flex items-center justify-between pt-3 mt-4 border-t border-slate-100">
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-red-600">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Ends: {{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, h:i A') }}</span>
            </div>

            @if(!$schedule->exam->is_paid || in_array($schedule->exam->sub_category_id, $subscribedCategoryIds))
                <button class="text-xs font-bold text-white bg-green-600 px-3 py-1.5 rounded hover:bg-green-700 transition-colors shadow-sm">
                    Attempt
                </button>
            @else
                <a href="{{ route('pricing') }}" class="flex items-center gap-1 text-xs font-bold transition-colors text-slate-500 hover:text-slate-800">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    Unlock
                </a>
            @endif
        </div>
    </div>
@endforeach
