@extends('layouts.student')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">

    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('student.exams.dashboard') }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Live Exams</h1>
    </div>

    {{-- Grid Container --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" id="exam-grid">
        {{-- Initial Data Loaded via Partial --}}
        @include('student.exams.partials.live_exam_card', ['schedules' => $schedules, 'subscription' => $subscription])
    </div>

    {{-- Load More Button --}}
    @if($schedules->hasMorePages())
        <div class="mt-10 text-center" id="load-more-container">
            <button id="load-more-btn"
                    class="flex items-center justify-center gap-2 px-6 py-3 mx-auto font-bold transition-all bg-white border shadow-sm border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 hover:shadow-md">
                <span>Load More Exams</span>
                <svg id="loading-icon" class="hidden w-5 h-5 text-slate-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    @endif

    {{-- Empty State --}}
    @if($schedules->count() == 0)
        <div class="py-20 text-center">
            <p class="font-medium text-slate-500">No live exams available at the moment.</p>
        </div>
    @endif

</div>

{{-- AJAX Script --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let page = 1;
        let loadMoreBtn = $('#load-more-btn');
        let loadingIcon = $('#loading-icon');

        loadMoreBtn.click(function() {
            page++;
            loadMoreBtn.prop('disabled', true);
            loadingIcon.removeClass('hidden');

            $.ajax({
                url: "{{ route('student.exams.fetch_live') }}?page=" + page,
                type: 'GET',
                success: function(response) {
                    // Append new HTML to grid
                    $('#exam-grid').append(response.html);

                    // Stop loading animation
                    loadMoreBtn.prop('disabled', false);
                    loadingIcon.addClass('hidden');

                    // If no more pages, hide button
                    if (!response.hasMore) {
                        $('#load-more-container').hide();
                    }
                },
                error: function() {
                    alert('Could not load more exams. Please try again.');
                    loadMoreBtn.prop('disabled', false);
                    loadingIcon.addClass('hidden');
                }
            });
        });
    });
</script>
@endsection
