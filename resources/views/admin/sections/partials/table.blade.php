<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <table class="w-full text-left">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Section Name</th>
                <th class="px-4 py-3 text-xs font-bold text-center text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-bold text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($sections as $section)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-4"><span class="px-2 py-1 bg-blue-50 text-[#0777be] text-[10px] font-mono rounded">{{ $section->code }}</span></td>
                <td class="px-4 py-4 font-medium text-gray-900">{{ $section->name }}</td>
                <td class="px-4 py-4 text-center">
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $section->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                        {{ $section->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.sections.edit', $section->id) }}" class="p-1.5 text-gray-500 hover:text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round"/></svg></a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $sections->links() }}</div>
</div>
