@foreach($exams as $exam)
    <div class="flex flex-col justify-between h-full p-4 transition-all bg-white border rounded-lg group border-slate-200 hover:border-blue-300 hover:shadow-sm">

        <div class="flex items-start justify-between gap-3 mb-2">
            <h3 class="text-sm font-bold text-slate-800 leading-snug group-hover:text-[var(--brand-blue)] line-clamp-2">
                {{ $exam->title }}
            </h3>
            @if($exam->is_paid && !in_array($exam->sub_category_id, $subscribedCategoryIds))
                <span class="shrink-0 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded border border-amber-200 bg-amber-50 text-amber-700">Paid</span>
            @else
                <span class="shrink-0 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded border border-green-200 bg-green-50 text-green-700">Open</span>
            @endif
        </div>

        <p class="text-[11px] font-semibold text-slate-500 mb-3">{{ $exam->subCategory->name }}</p>

        <div class="flex items-center justify-between pt-3 mt-auto border-t border-slate-100">
            <div class="flex items-center gap-3 text-xs font-medium text-slate-500">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $exam->duration }}m
                </span>
            </div>

            @if(!$exam->is_paid || in_array($exam->sub_category_id, $subscribedCategoryIds))
                <a href="#" class="text-xs font-bold text-white bg-[var(--brand-blue)] px-3 py-1.5 rounded hover:bg-blue-700 transition-colors shadow-sm">
                    Start
                </a>
            @else
                <a href="{{ route('pricing') }}" class="flex items-center gap-1 text-xs font-bold transition-colors text-slate-500 hover:text-slate-800">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    Unlock
                </a>
            @endif
        </div>
    </div>
@endforeach
