@php
    $routePrefix = request()->routeIs('instructor.*') ? 'instructor.' : 'admin.';
@endphp

<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    {{-- Compact Code Column --}}
                    <th class="w-20 px-3 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>

                    {{-- Question needs minimum width --}}
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase min-w-[250px]">Question
                    </th>

                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Section</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Skill</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Topic</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status
                    </th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($questions as $q)
                    <tr class="transition-colors hover:bg-gray-50/80 group">

                        {{-- Code (Reduced Padding & Font) --}}
                        <td class="px-3 py-3">
                            <span
                                class="px-1.5 py-0.5 font-mono text-[11px] font-medium bg-blue-50 text-[#0777be] rounded whitespace-nowrap">
                                {{ $q->code }}
                            </span>
                        </td>

                        {{-- Question --}}
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 line-clamp-2"
                                title="{{ strip_tags($q->question) }}">
                                {!! strip_tags($q->question) !!}
                            </div>
                        </td>

                        {{-- Type --}}
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-[#0777be] border border-blue-100 whitespace-nowrap">
                                {{ $q->questionType->code }}
                            </span>
                        </td>

                        {{-- Section --}}
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-600 whitespace-nowrap">{{ $q->section->name ?? '-' }}</span>
                        </td>

                        {{-- Skill --}}
                        <td class="px-4 py-3">
                            <span
                                class="text-xs font-medium text-gray-900 whitespace-nowrap">{{ $q->skill->name ?? '-' }}</span>
                        </td>

                        {{-- Topic (Added whitespace-nowrap) --}}
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded whitespace-nowrap">
                                {{ $q->topic->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3 text-center">
                            @if ($q->is_active)
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-[#94c940]/10 text-[#94c940] whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-[#94c940]"></span> Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-600 rounded-full bg-orange-50 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-orange-500"></span> Pending
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Preview --}}
                                <button @click="openPreview({{ $q->id }})"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-[#0777be] hover:border-[#0777be] hover:text-white group/btn"
                                    title="Preview">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>

                                {{-- Edit --}}
                                <a href="{{ route($routePrefix . 'questions.edit', $q->id) }}"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover-edit-btn group/btn"
                                    title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 transition-colors" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                {{-- Delete --}}
                                <button @click="deleteQuestion({{ $q->id }})"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-red-500 hover:border-red-500 hover:text-white group/btn"
                                    title="Delete">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="font-medium text-gray-500">No questions found matching your search.</p>
                                <button @click="search = ''; type = ''; status = ''; fetchQuestions()"
                                    class="mt-2 text-sm text-[#0777be] hover:underline">Clear filters</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($questions->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 pagination-wrapper">
            {{ $questions->links() }}
        </div>
    @endif
</div>
