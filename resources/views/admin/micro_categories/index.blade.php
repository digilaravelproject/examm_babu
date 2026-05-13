@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Micro Categories')
@section('header', 'Micro Categories')

@php
    // Route Helper Logic
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);

    $params = [];
    if (!$isAdmin && $currentRole) {
        $params = ['role' => $currentRole];
    }

    $urlIndex = route($routePrefix . 'micro-categories.index', $params);
    $urlCreate = route($routePrefix . 'micro-categories.create', $params);
@endphp

@section('content')
    {{-- Inline Styles for Pagination & Buttons --}}
    <style>
        .pagination-wrapper nav div { flex-wrap: wrap; }
        .hover-edit-btn:hover { background-color: #f062a4 !important; border-color: #f062a4 !important; color: white !important; }
        .hover-edit-btn:hover svg { color: white !important; }
        .hover-delete-btn:hover { background-color: #dc2626 !important; border-color: #dc2626 !important; color: white !important; }
        .hover-delete-btn:hover svg { color: white !important; }
        .btn-disabled { opacity: 0.5; cursor: not-allowed; background-color: #f3f4f6 !important; border-color: #e5e7eb !important; color: #9ca3af !important; }
    </style>

    <div x-data="microCatManagement('{{ $urlIndex }}')" x-init="init()" class="space-y-6">

        {{-- Header Section --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Micro Categories</h1>
                <p class="mt-1 text-sm text-gray-500">Manage specialized topics under sub-categories.</p>
            </div>
            <a href="{{ $urlCreate }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#0777be] rounded-lg shadow-md hover:bg-[#0666a3] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" />
                </svg>
                Add Micro-Category
            </a>
        </div>

        {{-- Filters Section --}}
        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" x-model="search" @input.debounce.500ms="applyFilter()"
                    class="w-full py-2.5 pl-10 pr-3 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20 placeholder-gray-400"
                    placeholder="Search micro-category name or code...">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                </svg>
            </div>

            <select x-model="sub_category_id" @change="applyFilter()"
                class="py-2.5 text-sm bg-gray-50 border-0 rounded-lg md:w-56 cursor-pointer hover:bg-gray-100">
                <option value="">All Sub-Categories</option>
                @foreach ($subCategories as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->category->name ?? 'N/A' }})</option>
                @endforeach
            </select>
        </div>

        {{-- Loading Spinner --}}
        <div x-show="loading" class="flex justify-center py-20 bg-white border border-gray-100 rounded-xl" style="display: none;">
            <div class="flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-500">Updating Results...</span>
            </div>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="table-container">
            @include('admin.micro_categories.partials.table', [
                'routePrefix' => $routePrefix,
                'routeParams' => $params
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function microCatManagement(baseUrl) {
            return {
                search: '',
                sub_category_id: '',
                loading: false,
                baseUrl: baseUrl,

                applyFilter() {
                    this.fetchData();
                },

                fetchData(page = 1) {
                    this.loading = true;
                    const params = new URLSearchParams();
                    params.append('page', page);
                    if(this.search) params.append('search', this.search);
                    if(this.sub_category_id) params.append('sub_category_id', this.sub_category_id);

                    fetch(`${this.baseUrl}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.text())
                    .then(html => {
                        document.getElementById('table-container').innerHTML = html;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.loading = false;
                    });
                },

                init() {
                    document.getElementById('table-container').addEventListener('click', (e) => {
                        let link = e.target.closest('.pagination-wrapper a');
                        if (link) {
                            e.preventDefault();
                            let urlParams = new URL(link.href).searchParams;
                            this.fetchData(urlParams.get('page'));
                        }
                    });
                }
            }
        }

        // Delete Confirmation Logic
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                html: `Deleting <b>"${name}"</b> cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
            });
        }
    </script>
@endpush
