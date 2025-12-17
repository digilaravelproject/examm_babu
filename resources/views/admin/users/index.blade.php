@extends('layouts.admin')

@section('header', 'User Management')

@section('content')
<div x-data="userManagement()" x-init="fetchUsers()" class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">All Users</h1>
            <p class="text-sm text-gray-500">Manage your students, instructors, and staff.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 transition-all bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                Import
            </button>
            <a href="#" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-all bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700">
                Add New User
            </a>
        </div>
    </div>

    <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex flex-col items-center gap-4 md:flex-row">

            <div class="relative w-full md:flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="search" @input.debounce.500ms="fetchUsers()"
                    class="block w-full py-2 pl-10 pr-3 text-gray-900 placeholder-gray-400 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Search by name, email...">
            </div>

            <div class="w-full md:w-48">
                <select x-model="role" @change="fetchUsers()"
                    class="block w-full py-2 pl-3 pr-10 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Roles</option>
                    @foreach($roles as $id => $name)
                        <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-48">
                <select x-model="status" @change="fetchUsers()"
                    class="block w-full py-2 pl-3 pr-10 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div x-show="loading" class="flex justify-center py-12">
        <svg class="w-8 h-8 text-blue-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <div x-show="!loading" class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl" id="users-table-container">
        </div>

</div>
@endsection

@push('scripts')
<script>
    // 1. Toast Notification Setup
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // 2. Alpine Logic
    function userManagement() {
        return {
            search: '', role: '', status: '', loading: false,

            fetchUsers(url = "{{ route('admin.users.index') }}") {
                this.loading = true;
                const params = new URLSearchParams();
                if(this.search) params.append('search', this.search);
                if(this.role) params.append('role', this.role);
                if(this.status) params.append('status', this.status);

                fetch(`${url}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('users-table-container').innerHTML = html;
                    this.loading = false;
                });
            },
            init() {
                // Handle Pagination Clicks inside the AJAX container
                document.getElementById('users-table-container').addEventListener('click', (e) => {
                    const link = e.target.closest('a.page-link');
                    if (link) {
                        e.preventDefault();
                        this.fetchUsers(link.getAttribute('href'));
                    }
                });
            }
        }
    }

    // 3. Status Toggle Function
    function toggleUserStatus(userId, userName, checkbox) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const statusText = data.new_status ? "Active" : "Inactive";
                Toast.fire({
                    icon: 'success',
                    title: `${userName} is now ${statusText}`
                });
            } else {
                checkbox.checked = !checkbox.checked; // Revert
                Toast.fire({ icon: 'error', title: 'Update failed!' });
            }
        })
        .catch(err => {
            checkbox.checked = !checkbox.checked;
            Toast.fire({ icon: 'error', title: 'Something went wrong!' });
        });
    }

    // 4. Delete User Function
    function deleteUser(id, userName) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete ${userName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        // Refresh Table
                        document.querySelector('[x-data="userManagement()"]').__x.$data.fetchUsers();
                        Toast.fire({
                            icon: 'success',
                            title: `${userName} is deleted successfully`
                        });
                    } else {
                        Toast.fire({ icon: 'error', title: 'Delete failed!' });
                    }
                });
            }
        });
    }
</script>
@endpush
