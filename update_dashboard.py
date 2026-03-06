import sys

file_path = r'c:\xampp_old\htdocs\Digi_Laravel_Prrojects\Exam-babu-new\exam_babu_live\resources\views\student\exams\dashboard.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """<div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">

    @php
        // 1. Get Attempts Taken
        $attemptsTaken = $attemptCounts[$schedule->id] ?? 0;

        // 2. Get Max Attempts from Settings (Default to 0/Unlimited if null)
        // Ensure 'settings' is cast to array/collection in Model or access via array key
        $settings = $schedule->exam->settings;
        $maxAttempts = $settings['no_of_attempts'] ?? 0;

        // 3. Logic: Lock only if Limit is set (>0) AND Taken >= Limit
        $isLimitReached = ($maxAttempts > 0 && $attemptsTaken >= $maxAttempts);
    @endphp

    @if(!$schedule->exam->is_paid || in_array($schedule->exam->micro_category_id, $subscribedCategoryIds))

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
             {{-- ... (Same as your old code) ... --}}
             <button disabled class="w-full py-2.5 text-xs font-bold text-slate-400 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed flex items-center justify-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Starts {{ $startDt->diffForHumans() }}
            </button>

        @elseif($isExpired)
            {{-- ❌ CONDITION 3: EXPIRED --}}
             {{-- ... (Same as your old code) ... --}}
             <button disabled class="w-full py-2.5 text-xs font-bold text-slate-400 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed">
                Test Closed
            </button>

        @else
            {{-- 🚀 CONDITION 4: LIVE / READY TO START --}}
            {{-- Show Attempt Button (Even if attempted before, as long as limit not reached) --}}
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
        {{-- ... (Same as your old code) ... --}}
         <a href="#" class="w-full inline-flex justify-center items-center py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
            Unlock Test
        </a>
    @endif
</div>"""

replacement = """<div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
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
</div>"""

if target in content:
    content = content.replace(target, replacement)
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Replacement successful.")
else:
    print("Target content not found in file.")
