@extends('layouts.admin')

@section('title', 'Comprehension Passages')
@section('header', 'Comprehension Passages')

@section('content')
    <style>
        /* Pagination buttons fix - Matching Question Module */
        .pagination-wrapper nav div {
            --tw-bg-opacity: 1 !important;
            background-color: rgb(255 255 255 / var(--tw-bg-opacity)) !important;
            color: rgb(55 65 81) !important;
        }

        .pagination-wrapper span,
        .pagination-wrapper a {
            background-color: white !important;
            color: #374151 !important;
            border-color: #e5e7eb !important;
        }

        .pagination-wrapper .active span {
            background-color: #0777be !important;
            color: white !important;
            border-color: #0777be !important;
        }

        /* Hover Edit Button Sync */
        .hover-edit-btn:hover {
            background-color: #f062a4 !important;
            border-color: #f062a4 !important;
            color: white !important;
        }
        .hover-edit-btn:hover svg { color: white !important; }
    </style>

    <div x-data="comprehensionManagement()" x-init="init()" class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Comprehension List</h1>
                <p class="mt-1 text-sm text-gray-500">Manage and organize your reading passages.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.comprehensions.create') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#0777be]/90 hover:shadow-lg shadow-[#0777be]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Passage
                </a>
            </div>
        </div>

        {{-- Filters (Sync UI with Questions) --}}
        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex flex-col gap-3 md:flex-row md:items-center">

                {{-- Search --}}
                <div class="relative w-full md:flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.500ms="applyFilter()"
                        class="block w-full py-2.5 pr-3 text-sm font-medium placeholder-gray-400 transition-all border-0 rounded-lg pl-10 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 text-gray-700 hover:bg-gray-100/80"
                        placeholder="Search by title or code...">
                </div>

                {{-- Status Filter --}}
                <div class="relative w-full md:w-48">
                    <select x-model="status" @change="applyFilter()"
                        class="w-full py-2.5 pl-3 pr-10 text-sm font-medium text-gray-600 bg-gray-50 border-0 rounded-lg appearance-none cursor-pointer hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="pending">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="flex justify-center py-20 bg-white border border-gray-100 rounded-xl" style="display: none;">
            <div class="flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-500">Updating...</span>
            </div>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="comprehensions-table-container">
            @include('admin.comprehensions.partials.table')
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        function comprehensionManagement() {
            return {
                search: '',
                status: '',
                loading: false,
                currentPage: 1,

                applyFilter() {
                    this.fetchData(1);
                },

                fetchData(page = 1) {
                    this.loading = true;
                    this.currentPage = page;

                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (this.search) params.append('search', this.search);
                    if (this.status) params.append('status', this.status);

                    const url = "{{ route('admin.comprehensions.index') }}";
                    const fetchUrl = `${url}?${params.toString()}`;

                    fetch(fetchUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(r => r.text())
                        .then(html => {
                            document.getElementById('comprehensions-table-container').innerHTML = html;
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            Toast.fire({ icon: 'error', title: 'Failed to load data' });
                            this.loading = false;
                        });
                },

                init() {
                    const container = document.getElementById('comprehensions-table-container');
                    container.addEventListener('click', (e) => {
                        const link = e.target.closest('.pagination-wrapper a');
                        if (link) {
                            e.preventDefault();
                            const url = new URL(link.href);
                            const page = url.searchParams.get('page');
                            if (page) this.fetchData(page);
                        }
                    });
                }
            }
        }
    </script>
@endpush
