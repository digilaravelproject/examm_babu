@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="examList()">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Exams Management</h1>
        <a href="{{ route('admin.exams.create') }}" class="bg-[#0777be] text-white px-4 py-2 rounded-lg font-bold shadow-md hover:bg-[#0666a3] transition">
            + Create New Exam
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-4 p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
        <input type="text" x-model="search" @input.debounce.500ms="fetchExams()" placeholder="Search Title or Code..." class="flex-1 text-sm border-gray-300 rounded-lg">
        <select x-model="type" @change="fetchExams()" class="w-48 text-sm border-gray-300 rounded-lg">
            <option value="">All Exam Types</option>
            @foreach($examTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table Container --}}
    <div class="relative">
        {{-- Loader --}}
        <div x-show="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-50">
            <div class="w-8 h-8 border-4 border-blue-500 rounded-full animate-spin border-t-transparent"></div>
        </div>

        <div id="exam-table">
            @include('admin.exams.partials.table')
        </div>
    </div>
</div>

<script>
    function examList() {
        return {
            search: '',
            type: '',
            loading: false,

            async fetchExams(url = "{{ route('admin.exams.index') }}") {
                this.loading = true;

                // Build Query Parameters
                let params = new URLSearchParams({
                    search: this.search,
                    type: this.type,
                });

                try {
                    const response = await fetch(`${url}${url.includes('?') ? '&' : '?'}${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const html = await response.text();
                    document.getElementById('exam-table').innerHTML = html;
                } catch (error) {
                    console.error('Error fetching exams:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }

    // Pagination links handling for AJAX
    document.addEventListener('click', function (e) {
        if (e.target.closest('.pagination-wrapper a')) {
            e.preventDefault();
            let url = e.target.closest('a').href;
            // Alpine function call from outside
            const alpineInstance = document.querySelector('[x-data="examList()"]').__x.$data;
            alpineInstance.fetchExams(url);
        }
    });
</script>
@endsection
