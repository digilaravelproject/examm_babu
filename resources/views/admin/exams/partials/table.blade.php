<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50/50">
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
                                <span class="text-sm font-bold text-gray-900 transition-colors group-hover:text-[var(--brand-blue)]">
                                    {{ $exam->title }}
                                </span>
                                <div class="flex items-center gap-2 mt-1.5">
                                    {{-- Exam Code Badge using Sky & Blue --}}
                                    <span class="px-2 py-0.5 font-mono text-[10px] font-bold rounded border border-[var(--brand-sky)]"
                                          style="background-color: rgba(127, 210, 234, 0.1); color: var(--brand-blue);">
                                        {{ $exam->code }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-medium">
                                        {{ $exam->examType->name ?? 'N/A' }} • {{ $exam->subCategory->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Sections Badge --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 border border-gray-200">
                                {{ $exam->exam_sections_count }} Sections
                            </span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-4 text-center">
                            @if($exam->is_active)
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset ring-opacity-30"
                                      style="background-color: rgba(148, 201, 64, 0.1); color: var(--brand-green); --tw-ring-color: var(--brand-green);">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full" style="background-color: var(--brand-green);"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-bold text-gray-500 ring-1 ring-inset ring-gray-500/20">
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
                                   class="flex items-center justify-center w-9 h-9 transition-all rounded-lg shadow-sm bg-white border border-gray-200 hover:text-white group/btn"
                                   onmouseover="this.style.backgroundColor='var(--brand-blue)'; this.style.borderColor='var(--brand-blue)';"
                                   onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#e5e7eb';"
                                   title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                {{-- Settings --}}
                                <a href="{{ route('admin.exams.settings', $exam->id) }}"
                                   class="flex items-center justify-center w-9 h-9 transition-all rounded-lg shadow-sm bg-white border border-gray-200 hover:bg-slate-800 hover:border-slate-800 hover:text-white group/btn"
                                   title="Settings">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Delete this exam?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                       class="flex items-center justify-center w-9 h-9 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-red-500 hover:border-red-500 hover:text-white group/btn"
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
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 mb-4 rounded-full bg-gray-50">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-base font-semibold text-gray-900">No exams found</p>
                                <p class="text-sm text-gray-500">Try adjusting your filters or search terms.</p>
                                <button @click="search = ''; type = ''; status = ''; fetchExams()"
                                        class="mt-4 text-sm font-bold transition-colors hover:underline"
                                        style="color: var(--brand-blue);">
                                    Clear all filters
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($exams->hasPages())
        <div class="px-4 py-4 bg-gray-50/50 border-t border-gray-200 pagination-wrapper">
            {{ $exams->links() }}
        </div>
    @endif
</div>
