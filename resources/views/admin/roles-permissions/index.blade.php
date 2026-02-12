@extends('layouts.admin')

@section('header', 'Advanced Access Control')

@section('content')
    {{-- Ensure Alpine is loaded --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    <div x-data="accessControl" class="space-y-6">

        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'roles'"
                    :class="{ 'border-[#0777be] text-[#0777be]': activeTab === 'roles', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'roles' }"
                    class="flex items-center gap-2 px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Roles Matrix
                </button>

                <button @click="activeTab = 'users'"
                    :class="{ 'border-[#0777be] text-[#0777be]': activeTab === 'users', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'users' }"
                    class="flex items-center gap-2 px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    User Overrides
                </button>
            </nav>
        </div>

        {{-- Error/Success Messages --}}
        @if (session('success'))
            <div class="p-4 text-green-700 border border-green-200 rounded-lg shadow-sm bg-green-50">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 text-red-700 border border-red-200 rounded-lg shadow-sm bg-red-50">{{ session('error') }}</div>
        @endif

        {{-- TAB 1: ROLES MATRIX --}}
        <div x-show="activeTab === 'roles'" x-transition.opacity>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Role Based Permissions</h2>
                <button @click="showCreateRoleModal = true"
                    class="bg-[#0777be] hover:bg-[#055a91] text-white px-4 py-2 rounded-lg text-sm shadow-md transition-all">
                    + Add Role
                </button>
            </div>

            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">
                                    Permission</th>
                                @foreach ($roles as $role)
                                    <th
                                        class="px-6 py-3 text-xs font-bold tracking-wider text-center text-gray-700 uppercase border-l">
                                        {{ $role->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($groupedPermissions as $groupName => $permissions)
                                <tr class="bg-blue-50/50">
                                    <td colspan="{{ count($roles) + 1 }}"
                                        class="px-6 py-2 text-xs font-bold text-[#0777be] uppercase">
                                        {{ ucfirst($groupName) }} Management
                                    </td>
                                </tr>
                                @foreach ($permissions as $permission)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-sm font-medium text-gray-700">
                                            {{ $permission->name }}
                                        </td>
                                        @foreach ($roles as $role)
                                            <td class="px-6 py-3 text-center border-l">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    {{-- Input Logic wahi hai, bas UI classes change kiye hain --}}
                                                    <input type="checkbox" class="sr-only peer"
                                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                        @change="updateRolePermission({{ $role->id }}, '{{ $permission->name }}', $event.target.checked)">

                                                    {{-- Updated Toggle UI --}}
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]">
                                                    </div>
                                                </label>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: USER OVERRIDES --}}
        <div x-show="activeTab === 'users'" x-transition.opacity style="display: none;">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                {{-- Left: Search User --}}
                <div class="p-6 bg-white border border-gray-200 shadow-sm md:col-span-1 rounded-xl h-fit">
                    <h3 class="mb-4 text-lg font-bold text-gray-800">Find User</h3>
                    <div class="relative">
                        {{-- Input Field --}}
                        <input type="text" x-model="searchQuery" @input.debounce.500ms="searchUsers()"
                            placeholder="Search by name or email..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0777be] focus:border-[#0777be] transition-all">

                        {{-- Search Icon --}}
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>

                        {{-- Loading Spinner --}}
                        <div x-show="isLoading" class="absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-[#0777be]" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    {{-- User List Results --}}
                    <div class="mt-4 space-y-2 max-h-[500px] overflow-y-auto">
                        <template x-for="user in userList" :key="user.id">
                            <div @click="selectUser(user.id)"
                                :class="{ 'bg-blue-50 border-[#0777be]': selectedUserId === user
                                    .id, 'bg-white border-transparent hover:bg-gray-50': selectedUserId !== user.id }"
                                class="flex items-center gap-3 p-3 transition-all border rounded-lg cursor-pointer">
                                <div class="flex items-center justify-center w-10 h-10 font-bold text-gray-600 bg-gray-200 rounded-full"
                                    x-text="user.name ? user.name.charAt(0) : 'U'"></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800" x-text="user.name"></p>
                                    <p class="text-xs text-gray-500" x-text="user.email"></p>
                                    <div class="flex gap-1 mt-1">
                                        <template x-for="role in user.roles">
                                            <span
                                                class="px-2 py-0.5 text-[10px] bg-gray-100 text-gray-600 rounded-full border border-gray-200"
                                                x-text="role.name"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="userList.length === 0 && searchQuery !== '' && !isLoading"
                            class="py-4 text-sm text-center text-gray-500">No users found.</div>
                    </div>
                </div>

                {{-- Right: Permissions List --}}
                <div class="md:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[500px]">
                    <div x-show="!selectedUserId" class="flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <p>Select a user to manage their specific permissions.</p>
                    </div>

                    <div x-show="selectedUserId" style="display: none;">
                        <div class="flex items-start justify-between pb-4 mb-6 border-b">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">User Permissions</h2>
                                <p class="text-sm text-gray-500">Managing access for <span
                                        class="font-bold text-[#0777be]" x-text="selectedUserName"></span></p>
                            </div>
                            <div class="space-y-1 text-xs text-right">
                                <div class="flex items-center justify-end gap-2"><span
                                        class="w-3 h-3 bg-green-100 border border-green-500 rounded-full"></span> Role
                                    Inherited (Active)</div>
                                <div class="flex items-center justify-end gap-2"><span
                                        class="w-3 h-3 bg-[#0777be] rounded-full"></span> Direct Assigned (Override)</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <template x-for="(perms, group) in userPermissions" :key="group">
                                <div class="p-4 border rounded-lg bg-gray-50/50">
                                    <h4 class="pb-2 mb-3 text-sm font-bold text-gray-700 uppercase border-b"
                                        x-text="group"></h4>
                                    <div class="space-y-3">
                                        <template x-for="perm in perms" :key="perm.name">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-600" x-text="perm.name"></span>

                                                <div class="flex items-center gap-2">
                                                    {{-- Case 1: Inherited --}}
                                                    <template x-if="perm.inherited_from_role">
                                                        <span
                                                            class="flex items-center gap-1 px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 border border-green-200 rounded cursor-help"
                                                            title="This permission comes from their Role. Cannot be revoked individually.">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                    clip-rule="evenodd"></path>
                                                            </svg>
                                                            Role Access
                                                        </span>
                                                    </template>

                                                    {{-- Case 2: Direct --}}
                                                    <template x-if="!perm.inherited_from_role">
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" class="sr-only peer"
                                                                :checked="perm.direct_permission"
                                                                @change="toggleUserPermission(perm.name, $event.target.checked)">
                                                            <div
                                                                class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0777be]">
                                                            </div>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Create Role Modal --}}
        <div x-show="showCreateRoleModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white shadow-xl rounded-xl" @click.away="showCreateRoleModal = false">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="flex justify-between px-6 py-4 border-b">
                        <h3 class="font-bold">New Role</h3>
                        <button type="button" @click="showCreateRoleModal = false">✕</button>
                    </div>
                    <div class="p-6">
                        <label class="block mb-1 text-sm">Role Name</label>
                        <input type="text" name="name" class="w-full p-2 border rounded" required
                            placeholder="manager">
                    </div>
                    <div class="p-4 text-right bg-gray-50 rounded-b-xl">
                        <button type="submit" class="bg-[#0777be] text-white px-4 py-2 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- SCRIPT FIXED --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('accessControl', () => ({
                activeTab: 'roles',
                showCreateRoleModal: false,
                searchQuery: '',
                userList: [],
                isLoading: false,
                selectedUserId: null,
                selectedUserName: '',
                userPermissions: {},

                // 1. Role Permission Update
                async updateRolePermission(roleId, permName, status) {
                    try {
                        const response = await fetch('{{ route('admin.roles.update_perm') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                role_id: roleId,
                                permission_name: permName,
                                status: status
                            })
                        });

                        if (!response.ok) throw new Error('Update failed');
                        this.toast('Role updated successfully');
                    } catch (e) {
                        console.error(e);
                        this.toast('Failed to update', 'error');
                    }
                },

                // 2. Search Users (FIXED URL)
                async searchUsers() {
                    if (this.searchQuery.length < 2) {
                        this.userList = [];
                        return;
                    }
                    this.isLoading = true;
                    try {
                        // Using Laravel Route in JS correctly
                        const url = '{{ route('admin.users.search') }}' + '?q=' +
                            encodeURIComponent(this.searchQuery);
                        const res = await fetch(url);
                        this.userList = await res.json();
                    } catch (e) {
                        console.error("Search Error:", e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                // 3. Select User & Fetch Permissions (FIXED URL)
                async selectUser(id) {
                    this.selectedUserId = id;
                    this.userPermissions = {}; // Reset UI

                    // Blade can't put JS ID into route(), so we assume the ID will be appended or we construct it.
                    // Best Practice: Route with Placeholder
                    let url = '{{ route('admin.users.get_perms', ':id') }}';
                    url = url.replace(':id', id);

                    try {
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Fetch failed');
                        const data = await res.json();

                        this.selectedUserName = data.user.name;
                        this.userPermissions = data.permissions;
                    } catch (e) {
                        console.error("Fetch User Perms Error:", e);
                        this.toast("Error loading permissions", "error");
                    }
                },

                // 4. Toggle User Direct Permission
                async toggleUserPermission(permName, isChecked) {
                    const action = isChecked ? 'give' : 'revoke';
                    try {
                        const res = await fetch('{{ route('admin.users.update_perm') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                user_id: this.selectedUserId,
                                permission_name: permName,
                                action: action
                            })
                        });

                        if (!res.ok) throw new Error();
                        this.toast(isChecked ? 'Permission assigned explicitly' :
                            'Direct permission revoked');
                    } catch (e) {
                        this.toast('Error updating permission', 'error');
                        // Reload to reset UI state
                        this.selectUser(this.selectedUserId);
                    }
                },

                // Helper: SweetAlert Toast
                toast(title, icon = 'success') {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        Toast.fire({
                            icon: icon,
                            title: title
                        });
                    } else {
                        console.log('Toast:', title); // Fallback if SweetAlert missing
                    }
                }
            }));
        });
    </script>
@endsection
