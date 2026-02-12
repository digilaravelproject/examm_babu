<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <table class="w-full text-left border-collapse">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Tag Name</th>
                <th class="px-6 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tags as $tag)
            <tr class="transition-colors hover:bg-gray-50/80">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-blue-500">#</span>
                        <span class="text-sm font-medium text-gray-900">{{ $tag->name }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($tag->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-[#94c940] border border-green-100">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-[#f062a4] border border-red-100">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.tags.edit', $tag->id) }}" class="p-1.5 text-gray-500 hover:text-[#0777be] hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round"/></svg>
                        </a>
                        <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-6 py-12 font-medium text-center text-gray-400">No tags found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($tags->hasPages())
        <div class="px-4 py-3 bg-white border-t pagination-wrapper">{{ $tags->links() }}</div>
    @endif
</div>
