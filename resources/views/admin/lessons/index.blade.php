@extends('layouts.admin')

@section('title', 'Lessons')
@section('header', 'Lessons')

@section('content')
    <style>
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
    </style>

    <div x-data="lessonManagement()" x-init="init()" class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Lessons List</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your learning content.</p>
            </div>
            <a href="{{ route('admin.lessons.create') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#0777be]/90 hover:shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Lesson
            </a>
        </div>

        {{-- Filters --}}
        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative w-full md:flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.500ms="applyFilter()"
                        class="block w-full py-2.5 pr-3 text-sm border-0 rounded-lg pl-10 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 text-gray-700 hover:bg-gray-100/80"
                        placeholder="Search by title, code...">
                </div>
                <div class="flex flex-col gap-2 md:flex-row">
                    <select x-model="skill" @change="applyFilter()"
                        class="w-full md:w-48 py-2.5 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20">
                        <option value="">Filter by Skill</option>
                        @foreach ($skills as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <select x-model="status" @change="applyFilter()"
                        class="w-full md:w-36 py-2.5 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20">
                        <option value="">Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Loading Spinner --}}
        <div x-show="loading" class="flex justify-center py-20 bg-white border border-gray-100 rounded-xl"
            style="display: none;">
            <div class="flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-sm font-medium text-gray-500">Loading...</span>
            </div>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="lessons-table-container">
            @include('admin.lessons.partials.lessons-table')
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

        function lessonManagement() {
            return {
                search: '',
                skill: '',
                status: '',
                loading: false,
                currentPage: 1,
                selectedItems: [],
                selectAll: false,

                init() {
                    const container = document.getElementById('lessons-table-container');
                    container.addEventListener('click', (e) => {
                        const link = e.target.closest('.pagination-wrapper nav a') || e.target.closest(
                            '.pagination-wrapper a');
                        if (link) {
                            e.preventDefault();
                            const url = new URL(link.href);
                            const page = url.searchParams.get('page');
                            if (page) this.fetchLessons(page);
                        }
                    });
                },

                applyFilter() {
                    this.fetchLessons(1);
                },

                fetchLessons(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    this.selectedItems = [];
                    this.selectAll = false;

                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (this.search) params.append('search', this.search);
                    if (this.skill) params.append('skill_id', this.skill);
                    if (this.status) params.append('status', this.status);

                    fetch("{{ route('admin.lessons.index') }}?" + params.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => {
                            document.getElementById('lessons-table-container').innerHTML = html;
                            this.loading = false;
                        })
                        .catch(() => {
                            Toast.fire({
                                icon: 'error',
                                title: 'Failed to load data'
                            });
                            this.loading = false;
                        });
                },

                toggleAll() {
                    this.selectAll = !this.selectAll;
                    const checkboxes = document.querySelectorAll('.lesson-checkbox');
                    this.selectedItems = [];
                    if (this.selectAll) {
                        checkboxes.forEach(cb => {
                            this.selectedItems.push(parseInt(cb.value));
                        });
                    }
                },

                bulkDelete() {
                    if (this.selectedItems.length === 0) return;

                    Swal.fire({
                        title: 'Delete ' + this.selectedItems.length + ' lessons?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete All'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.loading = true;
                            fetch("{{ route('admin.lessons.bulk_destroy') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            .content,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({
                                        ids: this.selectedItems
                                    })
                                })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        Toast.fire({
                                            icon: 'success',
                                            title: data.message
                                        });
                                        this.fetchLessons(this.currentPage);
                                    } else {
                                        Toast.fire({
                                            icon: 'error',
                                            title: data.message
                                        });
                                        this.loading = false;
                                    }
                                })
                                .catch(() => {
                                    Toast.fire({
                                        icon: 'error',
                                        title: 'Something went wrong'
                                    });
                                    this.loading = false;
                                });
                        }
                    });
                },

                deleteLesson(id) {
                    Swal.fire({
                        title: 'Delete Lesson?',
                        text: "Irreversible action!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete'
                    }).then(r => {
                        if (r.isConfirmed) {
                            // Using standard DELETE form submission style via fetch to match resource route
                            fetch(`/admin/lessons/${id}`, {
                                method: 'POST', // Method spoofing
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    _method: 'DELETE'
                                })
                            }).then(r => {
                                if (r.ok) {
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Deleted'
                                    });
                                    this.fetchLessons(this.currentPage);
                                } else {
                                    Toast.fire({
                                        icon: 'error',
                                        title: 'Error deleting'
                                    });
                                }
                            });
                        }
                    });
                }
            }
        }
    </script>
@endpush
