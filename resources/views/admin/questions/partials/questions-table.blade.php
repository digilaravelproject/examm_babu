@php
    // FIX: Fallback logic if variables are not passed from parent
    if (!isset($routePrefix)) {
        $isAdmin = request()->routeIs('admin.*');
        $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    }

    if (!isset($routeParams)) {
        $routeParams = [];
        if ($routePrefix === 'panel.') {
            $routeParams = ['role' => request()->route('role') ?? 'instructor'];
        }
    }
@endphp

<div class="relative overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">

    {{-- BULK ACTION BAR --}}
    <div x-show="selectedItems.length > 0" x-transition
        class="absolute top-0 left-0 z-10 flex items-center justify-between w-full px-4 py-3 border-b border-blue-100 bg-blue-50">
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

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" @click="toggleAll()"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-500">
                    </th>
                    <th class="w-20 px-3 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase min-w-[250px]">
                        Question</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Section</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Skill</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Topic</th>
                    @if (auth()->user()->hasRole('admin'))
                        <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Created By</th>
                    @endif
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status
                    </th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($questions as $q)
                    <tr class="transition-colors hover:bg-gray-50/80 group"
                        :class="{ 'bg-blue-50/50': selectedItems.includes({{ $q->id }}) }">

                        {{-- Checkbox --}}
                        <td class="px-4 py-3">
                            <input type="checkbox" value="{{ $q->id }}" x-model="selectedItems"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded cursor-pointer question-checkbox focus:ring-blue-500">
                        </td>

                        {{-- Code --}}
                        <td class="px-3 py-3">
                            <span onclick="copyToClipboard('{{ $q->code }}', this)"
                                class="inline-flex items-center gap-1
                                    px-1.5 py-0.5
                                    font-mono text-[11px] font-medium
                                    bg-blue-50 text-[#0777be]
                                    rounded whitespace-nowrap
                                    cursor-pointer hover:bg-blue-100 select-none"
                                title="Click to copy">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H12l-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>

                                {{ $q->code }}
                            </span>
                        </td>

                        {{-- Question --}}
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 line-clamp-2">
                                @if ($q->questionType->code === 'FIB')
                                    @php
                                        // 1. Clean HTML tags
                                        $cleanText = strip_tags($q->question);

                                        // 2. Replace ##Answer## with just the blank line (_______)
                                        $fibQuestion = preg_replace(
                                            '/##(.*?)##/',
                                            '<span class="font-bold text-gray-400">_______</span>',
                                            $cleanText,
                                        );
                                    @endphp
                                    {{-- Formatted FIB output --}}
                                    <span title="{{ $cleanText }}">{!! $fibQuestion !!}</span>
                                @else
                                    {{-- Normal Question output --}}
                                    <span title="{{ strip_tags($q->question) }}">
                                        {!! strip_tags($q->question) !!}
                                    </span>
                                @endif
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

                        {{-- Topic --}}
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded whitespace-nowrap">
                                {{ $q->topic->name ?? '-' }}
                            </span>
                        </td>
                        @if (auth()->user()->hasRole('admin'))
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($q->creator)
                                        {{-- Optional: Avatar dikhana ho to --}}
                                        {{-- <img src="{{ $q->creator->profile_photo_url }}" class="w-6 h-6 rounded-full"> --}}

                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-700">
                                                {{ $q->creator->fullname }}
                                            </span>
                                            {{-- Role Badge --}}
                                            <span class="text-[10px] text-gray-500">
                                                {{ $q->creator->roles->first()->name ?? 'Staff' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs italic text-gray-400">Unknown</span>
                                    @endif
                                </div>
                            </td>
                        @endif

                        {{-- Status (UPDATED TOGGLE BUTTON) --}}
                        <td class="px-4 py-3 text-center">
                            {{-- FIX: Passed $routeParams + id --}}
                            <form
                                action="{{ route($routePrefix . 'questions.toggle_status', array_merge($routeParams, ['id' => $q->id])) }}"
                                method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap transition-all duration-200 ease-in-out cursor-pointer hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500
                                    {{ $q->is_active
                                        ? 'bg-[#94c940]/10 text-[#94c940] hover:bg-red-50 hover:text-red-600 border border-transparent hover:border-red-200'
                                        : 'bg-orange-50 text-orange-600 hover:bg-[#94c940]/10 hover:text-[#94c940] border border-transparent hover:border-[#94c940]/30' }}"
                                    title="{{ $q->is_active ? 'Click to Deactivate' : 'Click to Activate' }}">

                                    @if ($q->is_active)
                                        {{-- Active State --}}
                                        <span
                                            class="w-1.5 h-1.5 mr-1.5 rounded-full bg-[#94c940] group-hover:bg-red-500"></span>
                                        <span>Active</span>
                                    @else
                                        {{-- Inactive State --}}
                                        <span
                                            class="w-1.5 h-1.5 mr-1.5 rounded-full bg-orange-500 group-hover:bg-[#94c940]"></span>
                                        <span>Inactive</span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- FIX: Passed $routeParams + id --}}
                                <a href="{{ route($routePrefix . 'questions.usage', array_merge($routeParams, ['id' => $q->id])) }}"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-purple-600 hover:border-purple-600 hover:text-white group/btn"
                                    title="Check Usage">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                        </path>
                                    </svg>
                                </a>
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
                                {{-- FIX: Passed $routeParams + question (Resource route uses model name) --}}
                                <a href="{{ route($routePrefix . 'questions.edit', array_merge($routeParams, ['question' => $q->id])) }}"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover-edit-btn group/btn"
                                    title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 transition-colors" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
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
                        <td colspan="9" class="px-6 py-12 text-center">
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
            {{-- FIX: Append routeParams to links so 'role' persists --}}
            {{ $questions->appends($routeParams)->links() }}
        </div>
    @endif
</div>

<script>
    function copyToClipboard(text, el) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = el.innerHTML;

            // Only text feedback, no color/class change
            el.innerHTML = 'Copied';

            setTimeout(() => {
                el.innerHTML = originalHTML;
            }, 900);
        });
    }
</script>
