<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <table class="w-full text-left">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Topic Name</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Skill</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($topics as $topic)
            <tr class="transition-colors hover:bg-gray-50">
                <td class="px-4 py-4">
                    <span class="px-2 py-1 bg-blue-50 text-[#0777be] text-[10px] font-mono font-bold rounded">
                        {{ $topic->code }}
                    </span>
                </td>
                <td class="px-4 py-4 font-medium text-gray-900">{{ $topic->name }}</td>
                <td class="px-4 py-4 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        {{ $topic->skill->name ?? 'N/A' }}
                    </span>
                </td>
                <td class="px-4 py-4 text-center">
                    @if($topic->is_active)
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-green-50 text-[#94c940] rounded-full border border-green-100">ACTIVE</span>
                    @else
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-red-50 text-[#f062a4] rounded-full border border-red-100">INACTIVE</span>
                    @endif
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.topics.edit', $topic->id) }}" class="p-1.5 text-gray-400 hover:text-[#0777be] hover:bg-blue-50 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round"/></svg>
                        </a>
                        <form action="{{ route('admin.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirm('Confirm deletion?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-12 text-center text-gray-400">No topics found matching filters.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($topics->hasPages())
        <div class="px-4 py-3 border-t bg-gray-50/50 pagination-wrapper">{{ $topics->links() }}</div>
    @endif
</div>
