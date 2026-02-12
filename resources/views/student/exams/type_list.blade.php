@extends('layouts.student')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">

    <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-200">
        <a href="{{ route('student.exams.dashboard') }}" class="transition-colors text-slate-400 hover:text-blue-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
        <h1 class="text-lg font-bold text-slate-900">{{ $type->name }}</h1>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" id="exam-grid">
        @include('student.exams.partials.exam_card', ['exams' => $exams, 'subscribedCategoryIds' => $subscribedCategoryIds])
    </div>

    {{-- Loading & Sentinel --}}
    <div id="loading-spinner" class="hidden py-8 text-center">
        <svg class="w-8 h-8 text-[var(--brand-blue)] animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
    </div>
    <div id="scroll-sentinel" class="h-10"></div>

    @if($exams->count() == 0)
        <div class="py-20 text-center border border-dashed rounded-xl bg-slate-50 border-slate-300">
            <p class="text-sm text-slate-500">No exams found.</p>
        </div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let page = 1;
        let hasMorePages = {{ $exams->hasMorePages() ? 'true' : 'false' }};
        let isLoading = false;
        const sentinel = document.getElementById('scroll-sentinel');
        const spinner = document.getElementById('loading-spinner');
        const grid = document.getElementById('exam-grid');

        if (!hasMorePages) { sentinel.style.display = 'none'; return; }

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

            // Dynamic Slug from Route is tricky in JS, so we use the URL directly
            // Or better, pass the fetch URL from backend
            let fetchUrl = "{{ route('student.exams.fetch_type', $type->slug) }}?page=" + page;

            fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
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
            .finally(() => { isLoading = false; spinner.classList.add('hidden'); });
        }
    });
</script>
@endsection
