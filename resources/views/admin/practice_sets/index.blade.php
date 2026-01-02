@extends('layouts.admin')

@section('title', 'Practice Sets')
@section('header', 'Practice Sets')

@section('content')
    <div x-data="practiceSetList()" x-init="init()" class="space-y-6">

        {{-- Header & Create Button --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Practice Sets</h1>
                <p class="mt-1 text-sm text-gray-500">Manage, organize, and publish practice sets.</p>
            </div>
            <a href="{{ route('admin.practice-sets.create') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#0777be] rounded-lg shadow-md hover:bg-[#0666a3] transition-all focus:ring-2 focus:ring-offset-2 focus:ring-[#0777be]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Practice Set
            </a>
        </div>

        {{-- Filter Section --}}
        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="col-span-1 md:col-span-2 relative">
                    <input type="text" x-model="search" @input.debounce.500ms="applyFilter()"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]"
                        placeholder="Search by Code or Title...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Sub Category Filter --}}
                <div>
                    <select x-model="sub_category_id" @change="applyFilter()"
                        class="w-full py-2.5 text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                        <option value="">All Sub Categories</option>
                        @foreach ($subCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Skill Filter --}}
                <div>
                    <select x-model="skill_id" @change="applyFilter()"
                        class="w-full py-2.5 text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                        <option value="">All Skills</option>
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="flex justify-center py-12 bg-white border border-gray-200 rounded-xl">
            <svg class="animate-spin h-8 w-8 text-[#0777be]" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        {{-- Table Container --}}
        <div x-show="!loading" id="table-container"
            class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
            @include('admin.practice_sets.partials.table')
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function practiceSetList() {
            return {
                search: '',
                sub_category_id: '',
                skill_id: '',
                loading: false,

                init() {
                    // Handle Pagination Clicks via Event Delegation
                    document.getElementById('table-container').addEventListener('click', (e) => {
                        const link = e.target.closest('a.page-link') || e.target.closest(
                        'nav[role="navigation"] a');
                        if (link) {
                            e.preventDefault();
                            this.fetchData(link.href);
                        }
                    });
                },

                applyFilter() {
                    // Reset to page 1 when filtering
                    const url = new URL("{{ route('admin.practice-sets.index') }}");
                    this.fetchData(url.toString());
                },

                fetchData(url) {
                    this.loading = true;

                    // Append current filter values to the URL
                    const fetchUrl = new URL(url);
                    if (this.search) fetchUrl.searchParams.set('search', this.search);
                    if (this.sub_category_id) fetchUrl.searchParams.set('sub_category_id', this.sub_category_id);
                    if (this.skill_id) fetchUrl.searchParams.set('skill_id', this.skill_id);

                    fetch(fetchUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('table-container').innerHTML = html;
                            this.loading = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.loading = false;
                        });
                }
            }
        }
    </script>
@endpush
