@extends('layouts.admin')
@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
<style>
    /* Pagination buttons ko hamesha light mode mein rakhne ke liye */
.pagination-wrapper nav div {
    --tw-bg-opacity: 1 !important;
    background-color: rgb(255 255 255 / var(--tw-bg-opacity)) !important;
    color: rgb(55 65 81) !important; /* Gray-700 */
}

/* Specific text aur arrow colors */
.pagination-wrapper span, .pagination-wrapper a {
    background-color: white !important;
    color: #374151 !important;
    border-color: #e5e7eb !important;
}
</style>
<div x-data="userManagement()" x-init="fetchUsers()" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">User List</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage and monitor all registered users.</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 text-sm font-semibold text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                Import
            </button>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 text-sm font-semibold text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 shadow-blue-200">
                Add New User
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-1 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex flex-col gap-2 p-1 md:flex-row">
            <div class="relative w-full md:flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" @input.debounce.500ms="fetchUsers()"
                    class="block w-full py-2 pr-3 text-sm font-medium placeholder-gray-400 transition-all border-transparent rounded-lg pl-9 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    placeholder="Search users by name, email...">
            </div>

            <select x-model="role" @change="fetchUsers()" class="w-full py-2 pl-3 pr-8 text-sm font-medium text-gray-600 border-transparent rounded-lg cursor-pointer md:w-44 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">All Roles</option>
                @foreach($roles as $id => $name) <option value="{{ $name }}">{{ ucfirst($name) }}</option> @endforeach
            </select>

            <select x-model="status" @change="fetchUsers()" class="w-full py-2 pl-3 pr-8 text-sm font-medium text-gray-600 border-transparent rounded-lg cursor-pointer md:w-44 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex justify-center py-20">
        <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Table Container -->
    <div x-show="!loading" id="users-table-container"></div>
</div>
@endsection

@push('scripts')
<script>
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true,
        didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
    });

    function userManagement() {
        return {
            search: '', role: '', status: '', loading: false,
            fetchUsers(url = "{{ route('admin.users.index') }}") {
                this.loading = true;
                const params = new URLSearchParams();
                if(this.search) params.append('search', this.search);
                if(this.role) params.append('role', this.role);
                if(this.status) params.append('status', this.status);

                const fetchUrl = url.includes('?') ? url : `${url}?${params.toString()}`;

                fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('users-table-container').innerHTML = html;
                    this.loading = false;
                });
            },
            init() {
                // AJAX Pagination Logic (Targets Laravel Default Links)
                document.getElementById('users-table-container').addEventListener('click', (e) => {
                    // Laravel pagination usually puts links in 'a' tags inside 'nav'
                    const link = e.target.closest('nav a') || e.target.closest('.pagination a');
                    if (link) {
                        e.preventDefault();
                        const href = link.getAttribute('href');
                        if (href && href !== '#') this.fetchUsers(href);
                    }
                });
            }
        }
    }

    function toggleUserStatus(id, name, el) {
        fetch(`/admin/users/${id}/toggle-status`, {
            method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json'}
        }).then(r=>r.json()).then(d=>{
            d.success ? Toast.fire({icon:'success', title: `${name} is now ${d.new_status?'Active':'Inactive'}`}) : (el.checked = !el.checked);
        }).catch(()=>{el.checked = !el.checked});
    }

    function deleteUser(id, name) {
        Swal.fire({
            title: 'Delete User?', text: `Permanently delete ${name}?`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b', confirmButtonText: 'Yes, Delete'
        }).then(r=>{
            if(r.isConfirmed) fetch(`/admin/users/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}})
            .then(r=>r.json()).then(d=>{ if(d.success) document.querySelector('[x-data="userManagement()"]').__x.$data.fetchUsers(); });
        });
    }
</script>
@endpush
