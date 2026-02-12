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
        <div x-show="showMappingModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showMappingModal" x-transition.opacity
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showMappingModal" x-transition.scale
                    class="inline-block w-full overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Map Sections</h3>
                                <div class="mt-4" id="mapping-content"></div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitMappingForm()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0777be] text-base font-medium text-white hover:bg-[#0666a3] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save
                            Changes</button>
                        <button type="button" @click="closeModal()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function subCatManagement(indexUrl, baseUrl) {
            return {
                search: '',
                category_id: '',
                loading: false,
                showMappingModal: false,
                baseUrl: baseUrl,

                applyFilter() {
                    this.fetchData();
                },

                fetchData(page = 1) {
                    this.loading = true;
                    let url = `${indexUrl}?page=${page}&search=${this.search}&category_id=${this.category_id}`;
                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text()).then(html => {
                            document.getElementById('table-container').innerHTML = html;
                            this.loading = false;
                        });
                },

                openMappingModal(subCatId) {
                    this.showMappingModal = true;
                    document.getElementById('mapping-content').innerHTML =
                        '<div class="flex justify-center py-10"><svg class="w-8 h-8 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>';

                    let url = `${this.baseUrl}/${subCatId}/sections`;
                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('mapping-content').innerHTML = html;
                        })
                        .catch(err => {
                            document.getElementById('mapping-content').innerHTML =
                                '<p class="text-red-500">Error loading data.</p>';
                        });
                },

                closeModal() {
                    this.showMappingModal = false;
                },

                submitMappingForm() {
                    const form = document.getElementById('section-mapping-form');
                    if (form) form.submit();
                },

                init() {
                    document.getElementById('table-container').addEventListener('click', (e) => {
                        let link = e.target.closest('.pagination-wrapper a');
                        if (link) {
                            e.preventDefault();
                            this.fetchData(new URL(link.href).searchParams.get('page'));
                        }

                        let mapBtn = e.target.closest('.map-sections-btn');
                        if (mapBtn) {
                            this.openMappingModal(mapBtn.dataset.id);
                        }
                    });
                }
            }
        }

        // --- UPDATED DELETE LOGIC ---
        function confirmDelete(id, name, microCount) {

            // 1. BLOCK DELETE
            if (microCount > 0) {
                Swal.fire({
                    title: '🚫 Action Blocked',
                    html: `
                        <div class="text-left text-sm text-gray-600">
                            <p class="mb-2">You cannot delete <b>"${name}"</b> because it has <b>${microCount}</b> linked Micro-Categories.</p>
                            <p class="text-red-500 font-bold">Please delete the micro-categories first.</p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'Okay, I understand',
                    confirmButtonColor: '#0777be'
                });
                return;
            }

            // 2. ALLOW DELETE
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

        // --- TOAST NOTIFICATIONS ---
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    </script>
@endpush
