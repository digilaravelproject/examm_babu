<div class="relative overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">

    {{-- BULK ACTION BAR --}}
    <div x-show="selectedItems.length > 0" x-transition
        class="absolute top-0 left-0 z-20 flex items-center justify-between w-full px-4 py-3 bg-blue-50 border-b border-blue-100">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-blue-600 rounded-full"
                x-text="selectedItems.length"></span>
            <span class="text-sm font-medium text-blue-900">items selected</span>
        </div>
        <button @click="bulkDelete()"
            class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                </path>
            </svg>
            Delete Selected
        </button>
    </div>

    <div class="overflow-x-auto min-h-[400px]">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    {{-- Checkbox Header --}}
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" @click="toggleAll()" x-model="selectAll"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    </th>

                    <th class="w-24 px-3 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase min-w-[200px]">Title
                    </th>
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
                @forelse($lessons as $lesson)
                    <tr class="transition-colors hover:bg-gray-50/80 group"
                        :class="{ 'bg-blue-50/50': selectedItems.includes({{ $lesson->id }}) }">

                        {{-- Checkbox Row --}}
                        <td class="px-4 py-3">
                            <input type="checkbox" value="{{ $lesson->id }}" x-model="selectedItems"
                                class="lesson-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        </td>

                        {{-- Code --}}
                        <td class="px-3 py-3">
                            <span
                                class="px-1.5 py-0.5 font-mono text-[11px] font-medium bg-blue-50 text-[#0777be] rounded whitespace-nowrap">
                                {{ $lesson->code }}
                            </span>
                        </td>

                        {{-- Title --}}
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 line-clamp-1" title="{{ $lesson->title }}">
                                {{ $lesson->title }}
                            </div>
                        </td>

                        {{-- Section --}}
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-600 whitespace-nowrap">
                                {{ $lesson->skill->section->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Skill --}}
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium text-gray-900 whitespace-nowrap">
                                {{ $lesson->skill->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Topic --}}
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded whitespace-nowrap">
                                {{ $lesson->topic->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3 text-center">
                            @if ($lesson->is_active)
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-[#94c940]/10 text-[#94c940] whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-[#94c940]"></span> Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 rounded-full bg-gray-100 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-gray-400"></span> Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.lessons.edit', $lesson->id) }}"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-[#0777be] hover:border-[#0777be] hover:text-white group"
                                    title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <button @click="deleteLesson({{ $lesson->id }})"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-red-500 hover:border-red-500 hover:text-white group"
                                    title="Delete">
                                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white" fill="none"
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
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p class="font-medium text-gray-500">No lessons found.</p>
                                <button @click="search = ''; skill = ''; status = ''; fetchLessons()"
                                    class="mt-2 text-sm text-[#0777be] hover:underline">Clear filters</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($lessons->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 pagination-wrapper">
            {{ $lessons->links() }}
        </div>
    @endif
</div>
