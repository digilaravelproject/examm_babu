@extends('layouts.admin')
@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
<style>
    /* Pagination buttons fix */
    .pagination-wrapper nav div {
        --tw-bg-opacity: 1 !important;
        background-color: rgb(255 255 255 / var(--tw-bg-opacity)) !important;
        color: rgb(55 65 81) !important;
    }
    .pagination-wrapper span, .pagination-wrapper a {
        background-color: white !important;
        color: #374151 !important;
        border-color: #e5e7eb !important;
    }
    /* Active Pagination Link Color Override */
    .pagination-wrapper .active span {
        background-color: #0777be !important;
        color: white !important;
        border-color: #0777be !important;
    }
</style>

<div x-data="userManagement()" x-init="fetchUsers()" class="space-y-6">

    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">User List</h1>
            <p class="mt-1 text-sm text-gray-500">Manage, monitor, and update user accounts.</p>
        </div>
        <div class="flex gap-3">
            {{-- Import Button --}}
            <button class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 transition-all bg-white border border-gray-300 rounded-lg shadow-sm hover:border-[#0777be] hover:text-[#0777be] hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Import
            </button>

            {{-- Add User Button (Brand Blue) --}}
            <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#0777be]/90 hover:shadow-lg shadow-[#0777be]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New User
            </a>
        </div>
    </div>

    <div class="p-1 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex flex-col gap-2 p-2 md:flex-row">

            {{-- Search Input --}}
            <div class="relative w-full md:flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" @input.debounce.500ms="fetchUsers()"
                    class="block w-full py-2.5 pr-3 text-sm font-medium placeholder-gray-400 transition-all border-transparent rounded-lg pl-9 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] hover:bg-gray-100 focus:hover:bg-white"
                    placeholder="Search users by name, email...">
            </div>

            {{-- Role Select --}}
            <div class="relative w-full md:w-48">
                <select x-model="role" @change="fetchUsers()"
                        class="w-full py-2.5 pl-3 pr-8 text-sm font-medium text-gray-600 border-transparent rounded-lg cursor-pointer bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] hover:bg-gray-100">
                    <option value="">All Roles</option>
                    @foreach($roles as $id => $name)
                        <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Select --}}
            <div class="relative w-full md:w-48">
                <select x-model="status" @change="fetchUsers()"
                        class="w-full py-2.5 pl-3 pr-8 text-sm font-medium text-gray-600 border-transparent rounded-lg cursor-pointer bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] hover:bg-gray-100">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div x-show="loading" class="flex justify-center py-20 bg-white border border-gray-100 rounded-xl">
        <div class="flex flex-col items-center gap-3">
            <svg class="w-10 h-10 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-500">Loading Users...</span>
        </div>
    </div>

    <div x-show="!loading" id="users-table-container" class="transition-opacity duration-300"></div>
</div>
@endsection

@push('scripts')
<script>
    // SweetAlert Configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // --- FLASH MESSAGES HANDLER ---
    @if(session('success'))
        Toast.fire({ icon: 'success', title: @json(session('success')) });
    @endif

    @if(session('error'))
        Toast.fire({ icon: 'error', title: @json(session('error')) });
    @endif

    @if($errors->any())
        Toast.fire({ icon: 'error', title: 'Please check the form for errors.' });
    @endif

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
                })
                .catch(() => {
                    Toast.fire({ icon: 'error', title: 'Failed to load data' });
                    this.loading = false;
                });
            },

            init() {
                // AJAX Pagination Logic
                document.getElementById('users-table-container').addEventListener('click', (e) => {
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

    // Toggle Status Function
    function toggleUserStatus(id, name, el) {
        fetch(`/admin/users/${id}/toggle-status`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json'}
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                // Using Exam Babu Green for success
                Toast.fire({icon: 'success', title: `${name} is now ${d.new_status ? 'Active' : 'Inactive'}`});
            } else {
                el.checked = !el.checked;
                Toast.fire({icon: 'error', title: d.message || 'Error updating status'});
            }
        })
        .catch(() => {
            el.checked = !el.checked;
            Toast.fire({icon: 'error', title: 'Connection error'});
        });
    }

    // Delete User Function
    function deleteUser(id, name) {
        Swal.fire({
            title: 'Delete User?',
            text: `Permanently delete ${name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete'
        }).then(r => {
            if(r.isConfirmed) {
                fetch(`/admin/users/${id}`, {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        Toast.fire({icon: 'success', title: 'User deleted successfully'});
                        document.querySelector('[x-data="userManagement()"]').__x.$data.fetchUsers();
                    } else {
                        Toast.fire({icon: 'error', title: d.message || 'Error deleting user'});
                    }
                })
                .catch(() => Toast.fire({icon: 'error', title: 'Connection error'}));
            }
        });
    }
</script>
@endpush
