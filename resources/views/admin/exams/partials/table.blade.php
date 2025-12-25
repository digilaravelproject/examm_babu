<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Exam Details</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Sections</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($exams as $exam)
                    <tr class="transition-colors hover:bg-gray-50/80 group">

                        {{-- Title & Meta --}}
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 group-hover:text-[#0777be] transition-colors">
                                    {{ $exam->title }}
                                </span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-1.5 py-0.5 font-mono text-[10px] font-medium text-[#0777be] bg-blue-50 border border-blue-100 rounded tracking-tight">
                                        {{ $exam->code }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wide">
                                        {{ $exam->examType->name ?? 'N/A' }} • {{ $exam->subCategory->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Sections Badge --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                {{ $exam->exam_sections_count }} Sections
                            </span>
                        </td>

                        {{-- Status Toggle Lookalike --}}
                        <td class="px-5 py-4 text-center">
                            @if($exam->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    <span class="w-1.5 h-1.5 mr-1.5 bg-green-500 rounded-full"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    <span class="w-1.5 h-1.5 mr-1.5 bg-gray-400 rounded-full"></span>
                                    Draft
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.exams.edit', $exam->id) }}"
                                   class="flex items-center justify-center w-8 h-8 transition-all rounded-lg shadow-sm bg-white border border-gray-200 hover:bg-[#0777be] hover:border-[#0777be] hover:text-white group/btn"
                                   title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                {{-- Settings --}}
                                <a href="{{ route('admin.exams.settings', $exam->id) }}"
                                   class="flex items-center justify-center w-8 h-8 transition-all rounded-lg shadow-sm bg-white border border-gray-200 hover:bg-gray-800 hover:border-gray-800 hover:text-white group/btn"
                                   title="Settings">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Delete this exam?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                       class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-red-500 hover:border-red-500 hover:text-white group/btn"
                                       title="Delete">
                                        <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="font-medium text-gray-500">No exams found matching your search.</p>
                                <button @click="search = ''; type = ''; status = ''; fetchExams()" class="mt-2 text-sm text-[#0777be] hover:underline">
                                    Clear filters
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($exams->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 pagination-wrapper">
            {{ $exams->links() }}
        </div>
    @endif
</div>
