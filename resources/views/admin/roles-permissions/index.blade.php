{{-- 1. Admin Layout ko Extend kiya --}}
@extends('layouts.admin')

{{-- 2. Header Title Set kiya --}}
@section('header', 'Roles & Permissions Matrix')

{{-- 3. Main Content Section --}}
@section('content')
    <div class="">
        <div class="max-w-[98%] mx-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <!-- Header Section inside the card -->
                <div class="p-6 bg-white border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Access Control</h3>
                        <p class="text-sm text-gray-500">Toggle switches to grant or revoke permissions.</p>
                    </div>
                    <div>
                        <!-- Optional: Add Role Button -->
                        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                            + Add New Role
                        </button>
                    </div>
                </div>

                <!-- Matrix Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- Empty corner cell -->
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">
                                    Permission Name
                                </th>
                                <!-- Roles Columns -->
                                @foreach($roles as $role)
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-l">
                                        {{ $role->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">

                            @foreach($groupedPermissions as $groupName => $permissions)
                                <!-- Group Header (e.g., User Management) -->
                                <tr class="bg-gray-100">
                                    <td colspan="{{ count($roles) + 1 }}" class="px-6 py-2 text-sm font-bold text-gray-800 uppercase">
                                        Manage {{ ucfirst($groupName) }}s
                                    </td>
                                </tr>

                                <!-- Permissions Rows -->
                                @foreach($permissions as $permission)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                        </td>

                                        <!-- Role Toggles -->
                                        @foreach($roles as $role)
                                            <td class="px-6 py-4 whitespace-nowrap text-center border-l">

                                                <!-- Alpine Component for Toggle -->
                                                <div x-data="{
                                                        active: {{ $role->hasPermissionTo($permission->name) ? 'true' : 'false' }},
                                                        loading: false
                                                     }">

                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" class="sr-only peer"
                                                               x-model="active"
                                                               @change="
                                                                    loading = true;
                                                                    fetch('{{ route('admin.roles_permissions.assign') }}', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                        },
                                                                        body: JSON.stringify({
                                                                            role_id: {{ $role->id }},
                                                                            permission_name: '{{ $permission->name }}',
                                                                            status: active
                                                                        })
                                                                    })
                                                                    .then(res => res.json())
                                                                    .then(data => {
                                                                        loading = false;
                                                                        // Optional: Show toast here
                                                                    })
                                                                    .catch(err => {
                                                                        loading = false;
                                                                        active = !active; // Revert if failed
                                                                        alert('Something went wrong');
                                                                    });
                                                               ">

                                                        <!-- Toggle UI -->
                                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                    </label>

                                                    <!-- Loading Indicator -->
                                                    <div x-show="loading" class="text-[10px] text-blue-500 mt-1" style="display: none;">
                                                        Saving...
                                                    </div>

                                                </div>
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
    </div>
@endsection
