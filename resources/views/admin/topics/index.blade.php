@extends('layouts.admin')

@section('title', 'Manage Topics')
@section('content')
<div x-data="topicManagement()" x-init="init()" class="space-y-6">
    <div class="flex items-center justify-between px-4 md:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Topics</h1>
            <p class="mt-1 text-sm text-gray-500">Manage sub-skills and detailed topics.</p>
        </div>
        <a href="{{ route('admin.topics.create') }}" class="px-4 py-2 bg-[#0777be] text-white rounded-lg text-sm font-bold shadow hover:bg-[#0666a3] transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round"/></svg>
            Add Topic
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-wrap gap-3">
        <input type="text" x-model="search" @input.debounce.500ms="applyFilter()" class="flex-1 min-w-[200px] py-2.5 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20" placeholder="Search topic or code...">

        <select x-model="skill_id" @change="applyFilter()" class="py-2.5 text-sm bg-gray-50 border-0 rounded-lg w-48 text-gray-600">
            <option value="">All Skills</option>
            @foreach($skills as $sk) <option value="{{ $sk->id }}">{{ $sk->name }}</option> @endforeach
        </select>

        <select x-model="status" @change="applyFilter()" class="py-2.5 text-sm bg-gray-50 border-0 rounded-lg w-40 text-gray-600">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <div id="table-container" x-show="!loading">@include('admin.topics.partials.table')</div>
    <div x-show="loading" class="flex justify-center py-20 bg-white border rounded-xl" style="display: none;">
        <svg class="animate-spin h-10 w-10 text-[#0777be]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
    </div>
</div>
@endsection

@push('scripts')
<script>
function topicManagement() {
    return {
        search: '', status: '', skill_id: '', loading: false,
        applyFilter() {
            this.loading = true;
            let url = `{{ route('admin.topics.index') }}?search=${this.search}&status=${this.status}&skill_id=${this.skill_id}`;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text()).then(html => {
                    document.getElementById('table-container').innerHTML = html;
                    this.loading = false;
                });
        },
        init() {
            document.getElementById('table-container').addEventListener('click', (e) => {
                let link = e.target.closest('.pagination-wrapper a');
                if (link) {
                    e.preventDefault();
                    let page = new URL(link.href).searchParams.get('page');
                    this.fetchData(page);
                }
            });
        }
    }
}
</script>
@endpush
