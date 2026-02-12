@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Question Bank')
@section('header', 'Question Bank')

@php
    // 1. Detect Role and Prefix
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);

    // 3. Prepare Parameters for Route Helper
    $params = [];
    if (!$isAdmin && $currentRole) {
        $params = ['role' => $currentRole];
    }

    $urlIndex       = route($routePrefix . 'questions.index', $params);
    $urlBulkDelete  = route($routePrefix . 'questions.bulk_destroy', $params);
    $urlCreateBase  = route($routePrefix . 'questions.create', $params); // Base create URL
@endphp

@section('content')
    <style>
        /* Pagination buttons fix */
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

        .hover-edit-btn:hover {
            background-color: #f062a4 !important;
            border-color: #f062a4 !important;
            color: white !important;
        }

        .hover-edit-btn:hover svg {
            color: white !important;
        }
    </style>

    <div x-data="questionManagement({
        baseUrl: '{{ $urlIndex }}',
        bulkDeleteUrl: '{{ $urlBulkDelete }}'
    })" x-init="init()" class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Question List</h1>
                <p class="mt-1 text-sm text-gray-500">Manage, review, and organize your question bank.</p>
            </div>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#0777be]/90 hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Question
                </button>
                <div x-show="open" @click.outside="open = false"
                    class="absolute right-0 z-50 w-48 py-2 mt-2 bg-white border border-gray-100 rounded-lg shadow-xl"
                    style="display: none;">
                    @foreach ($types as $type)

                        <a href="{{ $urlCreateBase }}?type={{ $type->code }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-[#0777be]">
                            {{ $type->name }} ({{ $type->code }})
                        </a>
                    @endforeach
                </div>
            </div>
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
                        placeholder="Search by question, code...">
                </div>
                <div class="flex flex-col gap-2 md:flex-row">
                    <select x-model="type" @change="applyFilter()"
                        class="w-full md:w-36 py-2.5 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20">
                        <option value="">Type</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->id }}">{{ $t->code }}</option>
                        @endforeach
                    </select>
                    <select x-model="skill" @change="applyFilter()"
                        class="w-full md:w-36 py-2.5 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20">
                        <option value="">Skill</option>
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                        @endforeach
                    </select>
                    <select x-model="status" @change="applyFilter()"
                        class="w-full md:w-36 py-2.5 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20">
                        <option value="">Status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Loading --}}
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
                <span class="text-sm font-medium text-gray-500">Updating...</span>
            </div>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="questions-table-container">

            @include('admin.questions.partials.questions-table', ['routePrefix' => $routePrefix, 'routeParams' => $params])
        </div>

        {{-- Preview Modal --}}
        <div x-show="previewOpen" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="previewOpen" class="fixed inset-0 transition-opacity" @click="previewOpen = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75 backdrop-blur-sm"></div>
                </div>
                <div
                    class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-16">
                    <div id="preview-content" class="min-h-[200px] bg-gray-50 flex items-center justify-center"></div>
                    <div class="flex justify-end px-6 py-4 border-t border-gray-100 bg-gray-50"><button
                            @click="previewOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Close</button>
                    </div>
                </div>
            </div>
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
        function questionManagement(config) {
            return {
                search: '',
                type: '',
                skill: '',
                status: '',
                loading: false,
                previewOpen: false,
                currentPage: 1,
                selectedItems: [],
                selectAll: false,

                // URLs ab config object se aayenge
                baseUrl: config.baseUrl,
                bulkDeleteUrl: config.bulkDeleteUrl,

                init() {
                    const container = document.getElementById('questions-table-container');
                    container.addEventListener('click', (e) => {
                        const link = e.target.closest('.pagination-wrapper nav a') || e.target.closest(
                            '.pagination-wrapper a');
                        if (link) {
                            e.preventDefault();
                            const url = new URL(link.href);
                            const page = url.searchParams.get('page');
                            if (page) this.fetchQuestions(page);
                        }
                    });
                },

                applyFilter() {
                    this.fetchQuestions(1);
                },

                fetchQuestions(page = 1) {
                    this.loading = true;
                    this.currentPage = page;
                    this.selectedItems = [];
                    this.selectAll = false;

                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (this.search) params.append('search', this.search);
                    if (this.type) params.append('type', this.type);
                    if (this.skill) params.append('skill', this.skill);
                    if (this.status) params.append('status', this.status);

                    fetch(this.baseUrl + "?" + params.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => {
                            document.getElementById('questions-table-container').innerHTML = html;
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

                // --- BULK ACTION LOGIC ---
                toggleAll() {
                    this.selectAll = !this.selectAll;
                    const checkboxes = document.querySelectorAll('.question-checkbox');
                    this.selectedItems = [];
                    if (this.selectAll) {
                        checkboxes.forEach(cb => {
                            this.selectedItems.push(parseInt(cb.value));
                        });
                    }
                },bulkDelete() {
                    if (this.selectedItems.length === 0) return;

                    Swal.fire({
                        title: 'Delete ' + this.selectedItems.length + ' items?',
                        text: "Safe items will be deleted. Linked items will be skipped.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Proceed'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.loading = true;

                            fetch(this.bulkDeleteUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    ids: this.selectedItems
                                })
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.loading = false;
                                
                                if (data.success) {
                                    // Case 1: Agar kuch items fail huye (Linked they)
                                    if (data.failed_items.length > 0) {
                                        
                                        // HTML List banana
                                        let listHtml = data.failed_items.map(item => 
                                            `<li class="flex flex-col pb-2 mb-2 border-b border-gray-200 last:border-0">
                                                <span class="font-bold text-gray-800 text-xs">${item.code}</span>
                                                <span class="text-xs text-red-500 italic">Linked in: ${item.exams}</span>
                                            </li>`
                                        ).join('');

                                        Swal.fire({
                                            title: 'Partial Deletion Report',
                                            html: `
                                                <div class="text-left">
                                                    <p class="text-green-600 font-bold mb-2">✅ Deleted: ${data.deleted_count} questions</p>
                                                    <p class="text-red-600 font-bold mb-2">❌ Skipped: ${data.failed_items.length} questions (Linked)</p>
                                                    
                                                    <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200 max-h-[200px] overflow-y-auto">
                                                        <ul class="list-none m-0 p-0">
                                                            ${listHtml}
                                                        </ul>
                                                    </div>
                                                </div>
                                            `,
                                            icon: 'info', // Info icon better fit karega
                                            confirmButtonText: 'Okay'
                                        });
                                    } 
                                    // Case 2: Sab kuch delete ho gaya
                                    else {
                                        Toast.fire({
                                            icon: 'success',
                                            title: `${data.deleted_count} questions deleted successfully.`
                                        });
                                    }
                                    
                                    // Table Refresh
                                    this.fetchQuestions(this.currentPage);
                                    this.selectedItems = []; // Selection clear
                                    this.selectAll = false;
                                } else {
                                    Toast.fire({ icon: 'error', title: data.message });
                                }
                            })
                            .catch(() => {
                                this.loading = false;
                                Toast.fire({ icon: 'error', title: 'Something went wrong' });
                            });
                        }
                    });
                },

                bulkDelete_old() {
                    if (this.selectedItems.length === 0) return;

                    Swal.fire({
                        title: 'Delete ' + this.selectedItems.length + ' items?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete All'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.loading = true;


                            fetch(this.bulkDeleteUrl, {
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
                                        this.fetchQuestions(this.currentPage);
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

                openPreview(id) {
                    this.previewOpen = true;
                    document.getElementById('preview-content').innerHTML =
                        '<div class="flex justify-center p-10"><svg class="w-8 h-8 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';

                    fetch(`${this.baseUrl}/${id}/preview`).then(r => r.text()).then(html => {
                        document.getElementById('preview-content').innerHTML = html;
                    });
                },
                deleteQuestion(id) {
                    Swal.fire({
                        title: 'Delete Question?',
                        text: "Irreversible action!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete'
                    }).then(r => {
                        if (r.isConfirmed) {
                            fetch(`${this.baseUrl}/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            }).then(r => r.json()).then(d => {
                                
                                // --- NEW CODE START: Handle Linked Logic ---
                                if (d.status === 'linked') {
                                    // Generate List HTML
                                    let examList = d.exams.map(e => `<li class="mb-1">${e}</li>`).join('');
                                    
                                    Swal.fire({
                                        title: '<span class="text-red-600">Cannot Delete!</span>',
                                        html: `
                                            <div class="text-left">
                                                <p class="mb-3 text-gray-600">This question is currently used in the following exams:</p>
                                                <ul class="p-3 mb-3 text-sm font-medium text-gray-800 bg-red-50 border border-red-100 rounded list-disc list-inside">
                                                    ${examList}
                                                </ul>
                                                <p class="text-sm italic text-gray-500">Please unlink the question from these exams first.</p>
                                            </div>
                                        `,
                                        icon: 'error',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Okay, Got it'
                                    });
                                    this.loading = false;
                                    return; // Stop execution
                                }
                                // --- NEW CODE END ---

                                if (d.success) {
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Deleted'
                                    });
                                    this.fetchQuestions(this.currentPage);
                                } else {
                                    Toast.fire({
                                        icon: 'error',
                                        title: d.message || 'Error'
                                    });
                                }
                            });
                        }
                    });
                },

                deleteQuestion_old(id) {
                    Swal.fire({
                        title: 'Delete Question?',
                        text: "Irreversible action!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete'
                    }).then(r => {
                        if (r.isConfirmed) {
                            fetch(`${this.baseUrl}/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            }).then(r => r.json()).then(d => {
                                if (d.success) {
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Deleted'
                                    });
                                    this.fetchQuestions(this.currentPage);
                                } else {
                                    Toast.fire({
                                        icon: 'error',
                                        title: 'Error'
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
