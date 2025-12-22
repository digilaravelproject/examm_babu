{{-- 1. Admin Layout ko Extend kiya --}}
@extends('layouts.admin')

{{-- 2. Header Title Set kiya --}}
@section('header', 'Roles & Permissions Matrix')

{{-- 3. Main Content Section --}}
@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Access Control</h1>
                <p class="text-sm text-gray-500 mt-1">Manage who can do what in the system.</p>
            </div>
            <button class="flex items-center px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#055a91] focus:ring-2 focus:ring-offset-2 focus:ring-[#0777be]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add New Role
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-slate-50">
                            <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-left text-gray-500 uppercase w-1/3">
                                Permission Name
                            </th>
                            @foreach($roles as $role)
                                <th scope="col" class="px-6 py-4 text-xs font-bold tracking-wider text-center text-gray-700 uppercase border-l border-gray-200 min-w-[120px]">
                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-[#0777be]">
                                        {{ $role->name }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">

                        @foreach($groupedPermissions as $groupName => $permissions)
                            <tr class="bg-slate-50/80">
                                <td colspan="{{ count($roles) + 1 }}" class="px-6 py-3 text-xs font-bold tracking-widest uppercase text-[#0777be]">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-[#94c940]"></div>
                                        Manage {{ ucfirst($groupName) }}s
                                    </div>
                                </td>
                            </tr>

                            @foreach($permissions as $permission)
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-700 whitespace-nowrap group-hover:text-gray-900">
                                        {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                    </td>

                                    @foreach($roles as $role)
                                        <td class="px-6 py-4 text-center border-l border-gray-100 whitespace-nowrap">

                                            <div x-data="{
                                                    active: {{ $role->hasPermissionTo($permission->name) ? 'true' : 'false' }},
                                                    loading: false,
                                                    async togglePermission() {
                                                        this.loading = true;
                                                        try {
                                                            const response = await fetch('{{ route('admin.roles_permissions.assign') }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                },
                                                                body: JSON.stringify({
                                                                    role_id: {{ $role->id }},
                                                                    permission_name: '{{ $permission->name }}',
                                                                    status: this.active
                                                                })
                                                            });

                                                            const data = await response.json();

                                                            if(response.ok) {
                                                                // SweetAlert Toast Notification
                                                                const Toast = Swal.mixin({
                                                                    toast: true,
                                                                    position: 'top-end',
                                                                    showConfirmButton: false,
                                                                    timer: 2000,
                                                                    timerProgressBar: false,
                                                                    didOpen: (toast) => {
                                                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                                                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                                                                    }
                                                                });

                                                                Toast.fire({
                                                                    icon: 'success',
                                                                    title: 'Permission Updated',
                                                                    color: '#0777be'
                                                                });
                                                            } else {
                                                                throw new Error('Failed');
                                                            }
                                                        } catch (err) {
                                                            this.active = !this.active; // Revert
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: 'Oops...',
                                                                text: 'Something went wrong!',
                                                                confirmButtonColor: '#0777be'
                                                            });
                                                        } finally {
                                                            this.loading = false;
                                                        }
                                                    }
                                                 }">

                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer"
                                                           x-model="active"
                                                           @change="togglePermission()">

                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                                                after:h-5 after:w-5 after:transition-all
                                                                peer-checked:bg-[#94c940]"></div>
                                                </label>

                                                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-white/50" style="display: none;">
                                                    <svg class="w-4 h-4 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
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
@endsection
