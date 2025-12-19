<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase w-14">No.</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">User Profile</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Username</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Role</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $index => $user)
                    <tr class="transition-colors hover:bg-gray-50/80 group">

                        <td class="px-5 py-4 text-center">
                            <span class="px-2 py-1 font-mono text-xs text-gray-400 transition-all rounded-md bg-gray-50 group-hover:bg-white group-hover:shadow-sm">
                                {{ $users->firstItem() + $loop->index }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-bold uppercase border-2 rounded-full text-[#0777be] border-[#0777be]/20 bg-[#0777be]/10">
                                    {{ substr($user->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900 group-hover:text-[#0777be] transition-colors">{{ $user->full_name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</div>
                                    @if($user->mobile)
                                        <div class="text-[10px] text-gray-400 mt-0.5 tracking-wide">{{ $user->mobile }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 bg-gray-50 text-gray-600 text-xs font-medium rounded border border-gray-200 font-mono group-hover:border-[#7fd2ea] transition-colors">
                                {{ $user->user_name }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            @foreach($user->roles as $role)
                                @php
                                    $badge = match($role->name) {
                                        'admin' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        'student' => 'bg-[#0777be]/10 text-[#0777be] ring-[#0777be]/20',
                                        'instructor' => 'bg-[#f062a4]/10 text-[#f062a4] ring-[#f062a4]/20',
                                        default => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>

                        <td class="px-5 py-4 text-center">
                            <div x-data="{ active: {{ $user->is_active ? 'true' : 'false' }} }" class="flex justify-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer"
                                        x-model="active"
                                        @change="toggleUserStatus({{ $user->id }}, '{{ $user->first_name }}', $el)">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer
                                        peer-checked:after:translate-x-full peer-checked:after:border-white
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                        after:bg-white after:border-gray-300 after:border after:rounded-full
                                        after:h-4 after:w-4 after:transition-all peer-checked:bg-[#94c940]">
                                    </div>
                                </label>
                            </div>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="flex items-center justify-center w-8 h-8 transition-all rounded-lg shadow-sm bg-white border border-gray-200 hover:bg-[#0777be] hover:border-[#0777be] hover:text-white group/btn"
                                   title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                <button onclick="deleteUser({{ $user->id }}, '{{ $user->first_name }}')"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-red-500 hover:border-red-500 hover:text-white group/btn"
                                    title="Delete">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="font-medium text-gray-500">No users found matching your search.</p>
                                <button @click="search = ''; role = ''; status = ''; fetchUsers()" class="mt-2 text-sm text-[#0777be] hover:underline">
                                    Clear filters
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif
</div>
