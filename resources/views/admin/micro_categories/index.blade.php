@extends('layouts.admin')

@section('title', 'Micro Categories')
@section('header', 'Micro Categories')

@section('content')
    <div x-data="microCatManagement()" x-init="init()" class="space-y-6">
        {{-- Header Section --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Micro Categories</h1>
                <p class="mt-1 text-sm text-gray-500">Manage specialized topics under sub-categories.</p>
            </div>
            <a href="{{ route('admin.micro-categories.create') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#0777be] rounded-lg shadow-md hover:bg-[#0666a3] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" />
                </svg>
                Add Micro-Category
            </a>
        </div>

        {{-- Filters Section (Alpine Linked) --}}
        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" x-model="search" @input.debounce.500ms="applyFilter()"
                    class="w-full py-2.5 pl-10 pr-3 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20"
                    placeholder="Search micro-category...">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                </svg>
            </div>

            <select x-model="sub_category_id" @change="applyFilter()"
                class="py-2.5 text-sm bg-gray-50 border-0 rounded-lg md:w-56">
                <option value="">All Sub-Categories</option>
                @foreach ($subCategories as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->category->name ?? 'N/A' }})</option>
                @endforeach
            </select>
        </div>

        {{-- Loading Spinner --}}
        <div x-show="loading" class="flex justify-center py-20 bg-white border rounded-xl" style="display: none;">
            <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="table-container">
            @include('admin.micro_categories.partials.table')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function microCatManagement() {
            return {
                search: '',
                sub_category_id: '',
                loading: false,

                applyFilter() {
                    this.fetchData();
                },

                fetchData(page = 1) {
                    this.loading = true;
                    // URL construct karna Micro Category ke liye
                    let url =
                        `{{ route('admin.micro-categories.index') }}?page=${page}&search=${this.search}&sub_category_id=${this.sub_category_id}`;

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
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
                    // Pagination Click Handler
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
    </script>
@endpush
