@extends('layouts.admin')

@section('title', 'Manage Plans')
@section('content')
    <div x-data="planManagement()" x-init="init()" class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Plans</h1>
            <button @click="openCreateDrawer = true"
                class="px-4 py-2 bg-[#10b981] text-white rounded-lg text-sm font-bold shadow hover:bg-[#059669]">
                NEW PLAN
            </button>
        </div>

        {{-- Filter Bar --}}
        <div class="p-1.5 bg-white border border-gray-200 shadow-sm rounded-xl flex flex-wrap gap-3">
            <input type="text" x-model="filters.name" @input.debounce.500ms="applyFilter()"
                class="flex-1 min-w-[200px] py-2 text-sm bg-gray-50 border-0 rounded-lg" placeholder="Search Name...">

            <select x-model="filters.category_id" @change="applyFilter()"
                class="w-48 py-2 text-sm border-0 rounded-lg bg-gray-50">
                <option value="">All Categories</option>
                @foreach ($subCategories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            <select x-model="filters.status" @change="applyFilter()"
                class="w-40 py-2 text-sm border-0 rounded-lg bg-gray-50">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        {{-- Table Container --}}
        <div id="table-container" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @include('admin.plans.partials.table')
        </div>

        {{-- Drawer --}}
        <div x-show="openCreateDrawer" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="openCreateDrawer = false"></div>
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-md bg-white shadow-xl p-6 overflow-y-auto">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">New Plan</h2>
                    @include('admin.plans.partials.create-form')
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
                applyFilter() {
                    let params = new URLSearchParams(this.filters).toString();
                    fetch(`{{ route('admin.plans.index') }}?${params}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => document.getElementById('table-container').innerHTML = html);
                }
            }
        }

        function deletePlan(id, name) {
            Swal.fire({
                title: 'Delete?',
                text: `Delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/plans/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success');
                            setTimeout(() => window.location.reload(),
                            1000); // Simple reload to refresh table
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    });
                }
            });
        }

        // Pagination Click Handler
        document.getElementById('table-container').addEventListener('click', (e) => {
            const link = e.target.closest('.pagination a') || e.target.closest('nav[role="navigation"] a');
            if (link) {
                e.preventDefault();
                const href = link.getAttribute('href');
                if (href && href !== '#') {
                    fetch(href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => document.getElementById('table-container').innerHTML = html);
                }
            }
        });
    </script>
@endpush
