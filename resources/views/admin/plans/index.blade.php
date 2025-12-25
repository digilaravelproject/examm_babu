@extends('layouts.admin')

@section('title', 'Manage Plans')
@section('content')
<div x-data="planManagement()" x-init="init()" class="space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Plans</h1>
        <button @click="openCreateDrawer = true" class="px-4 py-2 bg-[#10b981] text-white rounded-lg text-sm font-bold shadow hover:bg-[#059669] transition-colors">
            NEW PLAN
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-wrap gap-3">
        {{-- Search by Name/Code --}}
        <input type="text" x-model="filters.name" @input.debounce.500ms="applyFilter()" 
            class="flex-1 min-w-[200px] py-2 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-[#10b981]/20" 
            placeholder="Search Name or Code...">

        {{-- Category Filter --}}
        <select x-model="filters.category_id" @change="applyFilter()" class="w-48 py-2 text-sm border-0 rounded-lg bg-gray-50">
            <option value="">All Categories</option>
            @foreach($subCategories as $cat) 
                <option value="{{ $cat->id }}">{{ $cat->name }}</option> 
            @endforeach
        </select>

        {{-- Status Filter --}}
        <select x-model="filters.status" @change="applyFilter()" class="w-40 py-2 text-sm border-0 rounded-lg bg-gray-50">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    {{-- Table Container --}}
    <div id="table-container" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @include('admin.plans.partials.table')
    </div>

    {{-- Slide-over Drawer (Placeholder for Create Form) --}}
    <div x-show="openCreateDrawer" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="openCreateDrawer = false"></div>
        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
            <div class="w-screen max-w-md">
                <div class="h-full flex flex-col bg-white shadow-xl">
                    <div class="flex-1 h-0 overflow-y-auto p-6">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-gray-900">New Plan</h2>
                            <button @click="openCreateDrawer = false" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-6">
                            {{-- Add your Plan Form Here matching the screenshot inputs --}}
                            @include('admin.plans.partials.create-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function planManagement() {
    return {
        openCreateDrawer: false,
        filters: {
            name: '',
            status: '',
            category_id: ''
        },
        init() {
            // Initial load if needed
        },
        applyFilter() {
            // Construct query string based on filters
            let params = new URLSearchParams(this.filters).toString();
            let url = `{{ route('admin.plans.index') }}?${params}`;
            
            fetch(url, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest' } 
            })
            .then(r => r.text())
            .then(html => {
                document.getElementById('table-container').innerHTML = html;
            })
            .catch(error => console.error('Error fetching plans:', error));
        }
    }
}
</script>
@endpush