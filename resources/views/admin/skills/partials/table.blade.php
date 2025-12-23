<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <table class="w-full text-left">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Skill Name</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Section</th>
                <th class="px-4 py-3 text-xs font-bold text-center text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-bold text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($skills as $skill)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-4"><span class="px-2 py-1 bg-blue-50 text-[#0777be] text-[10px] font-mono rounded">{{ $skill->code }}</span></td>
                <td class="px-4 py-4 font-medium text-gray-900">{{ $skill->name }}</td>
                <td class="px-4 py-4 text-sm text-gray-600">{{ $skill->section->name }}</td>
                <td class="px-4 py-4 text-center">
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $skill->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                        {{ $skill->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.skills.edit', $skill->id) }}" class="p-1.5 text-gray-500 hover:text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round"/></svg>
                        </a>
                        <form action="{{ route('admin.skills.destroy', $skill->id) }}" method="POST" onsubmit="return confirm('Delete this skill?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-500 hover:text-red-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 pagination-wrapper">{{ $skills->links() }}</div>
</div>
