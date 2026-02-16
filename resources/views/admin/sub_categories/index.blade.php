@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Sub-Categories')
@section('header', 'Sub-Categories')

@php
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);
    $params = !$isAdmin && $currentRole ? ['role' => $currentRole] : [];

    $urlIndex = route($routePrefix . 'sub-categories.index', $params);
    $urlCreate = route($routePrefix . 'sub-categories.create', $params);
    $baseUrl = url($isAdmin ? 'admin/sub-categories' : "{$currentRole}/sub-categories");
@endphp

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div x-data="subCatManagement('{{ $urlIndex }}', '{{ $baseUrl }}')" x-init="init()" class="relative space-y-6">

        {{-- Header Section --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Sub-Categories</h1>
                <p class="mt-1 text-sm text-gray-500">Manage topics and sub-sections under primary categories.</p>
            </div>
            <a href="{{ $urlCreate }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#0777be] rounded-lg shadow-md hover:bg-[#0666a3] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" />
                </svg>
                Add Sub-Category
            </a>
        </div>

        {{-- Filters --}}
        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" x-model="search" @input.debounce.500ms="applyFilter()"
                    class="w-full py-2.5 pl-10 pr-3 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20"
                    placeholder="Search sub-category...">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                </svg>
            </div>
            <select x-model="category_id" @change="applyFilter()"
                class="py-2.5 text-sm bg-gray-50 border-0 rounded-lg md:w-48">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="flex justify-center py-20 bg-white border rounded-xl" style="display: none;">
            <div class="flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-500">Updating Results...</span>
            </div>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="table-container">
            @include('admin.sub_categories.partials.table', [
                'routePrefix' => $routePrefix,
                'routeParams' => $params,
            ])
        </div>

        {{-- MAPPING MODAL (Unchanged) --}}

    </div>
@endsection

@push('scripts')
    <script>
        function subCatManagement(indexUrl, baseUrl) {
            return {
                search: '',
                category_id: '',
                loading: false,

                init() {
                    // This handles the AJAX pagination clicks
                    document.addEventListener('click', (e) => {
                        let link = e.target.closest('.pagination-wrapper a');
                        if (link) {
                            e.preventDefault();
                            this.fetchData(new URL(link.href).searchParams.get('page'));
                        }
                    });
                },

                applyFilter() {
                    this.fetchData(1);
                },

                async fetchData(page = 1) {
                    this.loading = true;
                    try {
                        let url = new URL(indexUrl);
                        url.searchParams.set('page', page);
                        url.searchParams.set('search', this.search);
                        url.searchParams.set('category_id', this.category_id);

                        const response = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await response.text();
                        document.getElementById('table-container').innerHTML = html;
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        // Keep your existing confirmDelete function below
        function confirmDelete(id, name, microCount) {
            if (microCount > 0) {
                Swal.fire({
                    title: '🚫 Action Blocked',
                    html: `<div class="text-left text-sm text-gray-600">
                            <p class="mb-2">You cannot delete <b>"${name}"</b> because it has <b>${microCount}</b> linked Micro-Categories.</p>
                            <p class="text-red-500 font-bold">Please delete the micro-categories first.</p>
                        </div>`,
                    icon: 'error',
                    confirmButtonText: 'Okay, I understand',
                    confirmButtonColor: '#0777be'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
@endpush
