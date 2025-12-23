<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <table class="w-full text-left">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-xs font-bold tracking-widest text-gray-500 uppercase">Exam Details</th>
                <th class="px-6 py-4 text-xs font-bold tracking-widest text-center text-gray-500 uppercase">Sections</th>
                <th class="px-6 py-4 text-xs font-bold tracking-widest text-center text-gray-500 uppercase">Status</th>
                <th class="px-6 py-4 text-xs font-bold tracking-widest text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($exams as $exam)
            <tr class="transition-colors hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900">{{ $exam->title }}</span>
                        <span class="text-[10px] font-mono text-[#0777be] uppercase tracking-tighter">{{ $exam->code }}</span>
                        <span class="text-[10px] text-gray-400 mt-1 uppercase">{{ $exam->examType->name ?? 'N/A' }} | {{ $exam->subCategory->name ?? 'N/A' }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2 py-1 text-xs font-bold text-gray-600 bg-gray-100 rounded-lg">{{ $exam->exam_sections_count }} Sections</span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($exam->is_active)
                        <span class="px-2 py-1 text-[10px] font-bold bg-green-50 text-[#94c940] rounded-full border border-green-100">PUBLISHED</span>
                    @else
                        <span class="px-2 py-1 text-[10px] font-bold bg-orange-50 text-orange-500 rounded-full border border-orange-100">DRAFT</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.exams.edit', $exam->id) }}" class="p-2 text-gray-400 hover:text-[#0777be] hover:bg-blue-50 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round"/></svg>
                        </a>
                        <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Full exam data will be deleted. Proceed?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 transition-all rounded-lg hover:text-red-600 hover:bg-red-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-12 font-medium text-center text-gray-400">No exams found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 pagination-wrapper">{{ $exams->links() }}</div>
</div>
