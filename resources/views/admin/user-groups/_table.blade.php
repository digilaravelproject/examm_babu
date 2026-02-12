<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead class="border-b border-gray-200 bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Code</th>
                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Group Name</th>
                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Description</th>
                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">Visibility</th>
                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">Status</th>
                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($userGroups as $group)
                <tr class="transition-colors hover:bg-gray-50/80 group">
                    <td class="px-6 py-4">
                        {{-- Code Copy Logic Removed: Just plain display --}}
                        <span class="px-2 py-1 font-mono text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-md select-all">
                            {{ $group->code }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-gray-900 group-hover:text-[var(--brand-blue)] transition-colors">
                            {{ $group->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="block max-w-xs text-xs text-gray-500 truncate" title="{{ strip_tags($group->description) }}">
                            {{ Str::limit(strip_tags($group->description), 50) ?: '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($group->is_private)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-purple-100 text-purple-700 border border-purple-200">
                                Private
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">
                                Public
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($group->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-green-100 text-green-700 border border-green-200">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-500 border border-gray-200">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="editUserGroup({{ $group->id }})"
                                class="p-2 text-gray-400 hover:text-[var(--brand-blue)] hover:bg-[var(--brand-blue)]/10 rounded-xl transition-all"
                                title="Edit Group">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <form action="{{ route('admin.user-groups.destroy', $group->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this group? This action cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-2 text-gray-400 transition-all rounded-xl hover:text-red-600 hover:bg-red-50"
                                    title="Delete Group">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-gray-50">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">No User Groups Found</h3>
                            <p class="mt-1 text-xs text-gray-500">Get started by creating a new group.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($userGroups->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 pagination-wrapper">
        {{ $userGroups->links() }}
    </div>
@endif
