<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <table class="w-full text-left border-collapse">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Sub-Category</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Parent</th>
                <th class="px-4 py-3 text-xs font-bold text-center text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-bold text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($subCategories as $sub)
            <tr class="transition-colors hover:bg-gray-50/80">
                <td class="px-4 py-4"><span class="px-2 py-1 bg-blue-50 text-[#0777be] text-[10px] font-mono rounded">{{ $sub->code }}</span></td>
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        @if($sub->image_path)
                            <img src="{{ asset('storage/'.$sub->image_path) }}" class="object-cover w-10 h-10 rounded-lg">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-[#7fd2ea]/20 flex items-center justify-center text-[#0777be] font-bold text-lg">
                                {{ mb_substr($sub->name, 0, 1, 'UTF-8') }}
                            </div>
                        @endif
                        <span class="text-sm font-medium text-gray-900">{{ $sub->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">{{ $sub->category->name ?? 'N/A' }}</td>
                <td class="px-4 py-4 text-center">
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $sub->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                        {{ $sub->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.sub-categories.edit', $sub->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2"/></svg></a>
                        <form action="{{ route('admin.sub-categories.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-10 text-center text-gray-400">No sub-categories found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 pagination-wrapper">{{ $subCategories->links() }}</div>
</div>
