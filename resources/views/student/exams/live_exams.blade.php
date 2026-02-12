@extends('layouts.student')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-200">
        <a href="{{ route('student.exams.dashboard') }}" class="transition-colors text-slate-400 hover:text-blue-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
        <h1 class="text-lg font-bold text-slate-900">Live Exams</h1>
    </div>

    {{-- Grid Container --}}
    {{-- Notice: Logic yahan nahi, balki 'partials.live_exam_card' ke andar hai --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" id="exam-grid">
        @include('student.exams.partials.live_exam_card', ['schedules' => $schedules, 'subscribedCategoryIds' => $subscribedCategoryIds])
    </div>

    {{-- Loading Spinner (Shows when scrolling) --}}
    <div id="loading-spinner" class="hidden py-8 text-center">
        <svg class="w-8 h-8 text-[var(--brand-blue)] animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    {{-- Sentinel Element (Invisible trigger for scroll) --}}
    <div id="scroll-sentinel" class="h-10"></div>

    {{-- Empty State --}}
    @if($schedules->count() == 0)
        <div class="flex flex-col items-center justify-center py-20 border border-dashed bg-slate-50 border-slate-300 rounded-xl">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            <p class="text-sm font-medium text-slate-500">No live exams available.</p>
        </div>
    @endif

</div>

{{-- Infinite Scroll Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let page = 1;
        let hasMorePages = {{ $schedules->hasMorePages() ? 'true' : 'false' }};
        let isLoading = false;

        const sentinel = document.getElementById('scroll-sentinel');
        const spinner = document.getElementById('loading-spinner');
        const grid = document.getElementById('exam-grid');

        if (!hasMorePages) {
            sentinel.style.display = 'none';
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && hasMorePages && !isLoading) {
                    loadMoreExams();
                }
            });
        }, { rootMargin: '100px' });

        observer.observe(sentinel);

        function loadMoreExams() {
            isLoading = true;
            spinner.classList.remove('hidden');
            page++;

            fetch(`{{ route('student.exams.fetch_live') }}?page=${page}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    grid.insertAdjacentHTML('beforeend', data.html);

                    hasMorePages = data.hasMore;
                    if (!hasMorePages) {
                        observer.unobserve(sentinel);
                        sentinel.style.display = 'none';
                    }
                }
            })
            .catch(err => console.error('Error loading exams:', err))
            .finally(() => {
                isLoading = false;
                spinner.classList.add('hidden');
            });
        }
    });
</script>
@endsection
