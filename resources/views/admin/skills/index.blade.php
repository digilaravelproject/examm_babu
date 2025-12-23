@extends('layouts.admin')

@section('title', 'Manage Skills')
@section('content')
<div x-data="skillManagement()" x-init="init()" class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Skills</h1>
        <a href="{{ route('admin.skills.create') }}" class="px-4 py-2 bg-[#0777be] text-white rounded-lg text-sm font-bold shadow hover:bg-[#0666a3]">Add Skill</a>
    </div>

    {{-- Filter Bar --}}
    <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-wrap gap-3">
        <input type="text" x-model="search" @input.debounce.500ms="applyFilter()" class="flex-1 min-w-[200px] py-2 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#0777be]/20" placeholder="Search skill or code...">

        <select x-model="section_id" @change="applyFilter()" class="w-48 py-2 text-sm border-0 rounded-lg bg-gray-50">
            <option value="">All Sections</option>
            @foreach($sections as $sec) <option value="{{ $sec->id }}">{{ $sec->name }}</option> @endforeach
        </select>

        <select x-model="status" @change="applyFilter()" class="w-40 py-2 text-sm border-0 rounded-lg bg-gray-50">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <div id="table-container">@include('admin.skills.partials.table')</div>
</div>
@endsection

@push('scripts')
<script>
function skillManagement() {
    return {
        search: '', status: '', section_id: '',
        applyFilter() {
            let url = `{{ route('admin.skills.index') }}?search=${this.search}&status=${this.status}&section_id=${this.section_id}`;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text()).then(html => document.getElementById('table-container').innerHTML = html);
        }
    }
}
</script>
@endpush
