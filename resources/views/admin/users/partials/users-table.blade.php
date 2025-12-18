<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase w-14">No.</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">User Profile</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Username</th> <!-- Separate Column -->
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Role</th> <!-- Separate Column -->
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $index => $user)
                    <tr class="transition-colors hover:bg-gray-50">

                        <!-- 1. SR NO -->
                        <td class="px-5 py-4 text-center">
                            <span class="px-2 py-1 font-mono text-xs text-gray-400 rounded-md bg-gray-50">
                                {{ $users->firstItem() + $loop->index }}
                            </span>
                        </td>

                        <!-- 2. USER PROFILE (Name + Email + Mobile) -->
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-bold text-blue-600 border border-blue-100 rounded-full bg-blue-50">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $user->full_name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</div>
                                    @if($user->mobile)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $user->mobile }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- 3. USERNAME (Separate) -->
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded border border-gray-200 font-mono">
                                {{ $user->user_name }}
                            </span>
                        </td>

                        <!-- 4. ROLE (Separate) -->
                        <td class="px-5 py-4">
                            @foreach($user->roles as $role)
                                @php
                                    $badge = match($role->name) {
                                        'admin' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        'student' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
                                        'instructor' => 'bg-purple-50 text-purple-700 ring-purple-700/10',
                                        default => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>

                        <!-- 5. STATUS -->
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
                                        after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500">
                                    </div>
                                </label>
                            </div>
                        </td>

                        <!-- 6. ACTIONS (With SVG Icons) -->
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <!-- Edit Button -->
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="flex items-center justify-center w-8 h-8 transition-all rounded-full shadow-sm group bg-blue-50 hover:bg-blue-600" title="Edit">
                                    <svg class="w-4 h-4 text-blue-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                <!-- Delete Button -->
                                <button onclick="deleteUser({{ $user->id }}, '{{ $user->first_name }}')"
                                    class="flex items-center justify-center w-8 h-8 transition-all rounded-full shadow-sm group bg-red-50 hover:bg-red-600" title="Delete">
                                    <svg class="w-4 h-4 text-red-600 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No users found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Default Pagination (AJAX Ready) -->
@if($users->hasPages())
    <div class="px-4 py-3 bg-white border-t border-gray-200 pagination-wrapper">
        {{ $users->links() }}
    </div>
@endif
</div>
