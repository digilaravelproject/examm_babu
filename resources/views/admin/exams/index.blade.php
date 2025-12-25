@extends('layouts.admin')
@section('title', 'Exams Management')
@section('header', 'Exams Management')

@section('content')
<style>
    /* Pagination buttons fix from User Management */
    .pagination-wrapper nav div {
        --tw-bg-opacity: 1 !important;
        background-color: rgb(255 255 255 / var(--tw-bg-opacity)) !important;
        color: rgb(55 65 81) !important;
    }
    .pagination-wrapper span, .pagination-wrapper a {
        background-color: white !important;
        color: #374151 !important;
        border-color: #e5e7eb !important;
    }
    /* Active Pagination Link Color Override */
    .pagination-wrapper .active span {
        background-color: #0777be !important;
        color: white !important;
        border-color: #0777be !important;
    }
</style>

<div x-data="examList()" x-init="init()" class="space-y-6">

    {{-- Header Section --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Exam List</h1>
            <p class="mt-1 text-sm text-gray-500">Create, manage and publish online exams.</p>
        </div>

        {{-- Create Button --}}
        <a href="{{ route('admin.exams.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#0777be]/90 hover:shadow-lg shadow-[#0777be]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Create New Exam
        </a>
    </div>

    {{-- Filters Section --}}
    <div class="p-1 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex flex-col gap-2 p-2 md:flex-row">

            {{-- Search Input --}}
            <div class="relative w-full md:flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" @input.debounce.500ms="fetchExams()"
                    class="block w-full py-2.5 pr-3 text-sm font-medium placeholder-gray-400 transition-all border-transparent rounded-lg pl-9 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] hover:bg-gray-100 focus:hover:bg-white"
                    placeholder="Search by Title or Code...">
            </div>

            {{-- Type Filter --}}
            <div class="relative w-full md:w-48">
                <select x-model="type" @change="fetchExams()"
                        class="w-full py-2.5 pl-3 pr-8 text-sm font-medium text-gray-600 border-transparent rounded-lg cursor-pointer bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] hover:bg-gray-100">
                    <option value="">All Types</option>
                    @foreach($examTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="relative w-full md:w-48">
                <select x-model="status" @change="fetchExams()"
                        class="w-full py-2.5 pl-3 pr-8 text-sm font-medium text-gray-600 border-transparent rounded-lg cursor-pointer bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] hover:bg-gray-100">
                    <option value="">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="flex justify-center py-20 bg-white border border-gray-100 rounded-xl">
        <div class="flex flex-col items-center gap-3">
            <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-500">Loading Exams...</span>
        </div>
    </div>

    {{-- Table Container --}}
    <div x-show="!loading" id="exam-table-container" class="transition-opacity duration-300">
        @include('admin.exams.partials.table')
    </div>
</div>
@endsection

@push('scripts')
<script>
    function examList() {
        return {
            search: '',
            type: '',
            status: '',
            loading: false,

            fetchExams(url = "{{ route('admin.exams.index') }}") {
                this.loading = true;
                const params = new URLSearchParams();
                if(this.search) params.append('search', this.search);
                if(this.type) params.append('type', this.type);
                if(this.status) params.append('status', this.status);

                // Handle URL formatting correctly
                const fetchUrl = url.includes('?')
                    ? `${url}&${params.toString()}`
                    : `${url}?${params.toString()}`;

                fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('exam-table-container').innerHTML = html;
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                    console.error('Failed to load data');
                });
            },

            init() {
                // Event Delegation for Pagination Links
                // This catches clicks inside the table container
                document.getElementById('exam-table-container').addEventListener('click', (e) => {
                    const link = e.target.closest('.pagination-wrapper a');
                    if (link) {
                        e.preventDefault();
                        const href = link.getAttribute('href');
                        if (href && href !== '#') {
                            this.fetchExams(href);
                        }
                    }
                });
            }
        }
    }
</script>
@endpush
