
<div class="mb-8">
    <div class="flex items-end justify-between px-2 mb-2">
        <p class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">Current Goal</p>
        <a href="{{ route('student.change_syllabus') }}" class="text-[10px] font-bold text-blue-600 hover:underline">Change</a>
    </div>

    <div class="relative block w-full group">

        {{-- CASE A: Syllabus Selected (Shown via Alpine logic) --}}
        <template x-if="currentSyllabus">
            <div class="flex items-center justify-between w-full px-4 py-3 text-sm font-bold transition-all transform border border-blue-200 shadow-sm text-slate-800 bg-blue-50 rounded-xl">
                <div class="flex items-center gap-3 truncate">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span x-text="currentSyllabus" class="truncate max-w-[130px]"></span>
                </div>
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </template>

        {{-- CASE B: No Syllabus Selected --}}
        <template x-if="!currentSyllabus">
            <a href="{{ route('student.change_syllabus') }}" class="flex items-center justify-between w-full px-4 py-3 text-sm font-bold text-red-700 transition-all transform shadow-md rounded-xl hover:scale-[1.02] bg-red-50 border border-red-200 animate-pulse">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Select Goal Now</span>
                </div>
                <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </template>
    </div>
</div>

{{--
    2. MAIN NAVIGATION
    Fixed: Route conflicts (Strict checks).
--}}
<div class="space-y-1.5">

    {{-- Dashboard --}}
    <a href="{{ route('student.dashboard') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('student.dashboard') ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('student.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        Overview
    </a>

    {{-- My Exams (Strict Check: Only shows for main dashboard or types) --}}
    <a href="{{ route('student.exams.dashboard') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ (request()->routeIs('student.exams.dashboard') || request()->routeIs('student.exams.type')) ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ (request()->routeIs('student.exams.dashboard') || request()->routeIs('student.exams.type')) ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        My Exams
    </a>

    {{-- Live Exams (Strict Check: Only for live route) --}}
    <a href="{{ route('student.exams.live') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('student.exams.live') ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('student.exams.live') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Live Tests
        {{-- Live Pulse Dot --}}
        <span class="relative flex w-2 h-2 ml-auto">
          <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
          <span class="relative inline-flex w-2 h-2 bg-red-500 rounded-full"></span>
        </span>
    </a>

    {{-- Add Exams --}}
    <a href="{{ route('student.add_exams') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('student.add_exams') ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('student.add_exams') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Add More Exams
    </a>

    {{-- Subscriptions --}}
    <a href="{{ route('student.subscriptions.index') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('student.subscriptions.*') ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('student.subscriptions.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        My Subscriptions
    </a>

    {{-- Payment History --}}
    <a href="{{ route('student.payments.index') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('student.payments.*') ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('student.payments.*') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Payment History
    </a>

    {{-- Exam Demo (Static) --}}
    <a href="{{ route('student.exam_demo') }}"
       class="flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('student.exam_demo') ? 'bg-[var(--brand-blue)] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('student.exam_demo') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        Demo Interface
    </a>

</div>
