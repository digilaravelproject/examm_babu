@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50/50" x-data="questionManager()">

    <div class="max-w-[1400px] mx-auto py-4 px-4 sm:px-6 lg:px-8 lg:py-6">

        {{-- 1. Wizard Steps --}}
        <div class="mb-6 lg:mb-8 overflow-x-auto pb-2">
            @include('admin.exams.partials._steps', ['activeStep' => 'questions'])
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- 2. LEFT SIDEBAR: Section Selector --}}
            <div class="lg:col-span-3 space-y-4">

                {{-- Summary Stats (Mobile Friendly) --}}
                <div class="bg-gradient-to-br from-[#0777be] to-[#0666a3] rounded-xl shadow-lg p-4 sm:p-5 text-white flex justify-between items-center lg:block">
                    <div>
                        <p class="text-xs font-medium text-blue-100 uppercase mb-1">Total Questions</p>
                        <div class="flex items-baseline gap-1">
                            <h2 class="text-2xl sm:text-3xl font-extrabold" x-text="totalQuestions">0</h2>
                            <span class="text-sm text-blue-200 hidden sm:inline">Added</span>
                        </div>
                    </div>
                    <div class="lg:hidden text-right">
                        <span class="text-xs bg-white/20 px-2 py-1 rounded text-white" x-text="`${examSections.length} Sections`"></span>
                    </div>
                </div>

                {{-- Section List Card --}}
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden lg:sticky lg:top-6">
                    {{-- Header --}}
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Select Section</h3>
                        <span class="text-[10px] bg-blue-100 text-[#0777be] px-2 py-0.5 rounded-full font-bold hidden lg:inline-block">{{ $examSections->count() }}</span>

                        {{-- Mobile Dropdown Toggle (Visual cue only, logic is simple list) --}}
                        <span class="lg:hidden text-xs text-gray-400">Tap to switch</span>
                    </div>

                    {{-- Scrollable List for Mobile --}}
                    <div class="p-2 space-y-1 max-h-48 lg:max-h-none overflow-y-auto lg:overflow-visible">
                        @foreach($examSections as $section)
                            <button
                                @click="currentSectionId = {{ $section->id }}; loadSectionQuestions()"
                                class="w-full text-left px-3 py-3 rounded-lg text-sm font-medium transition-all flex items-center justify-between group"
                                :class="currentSectionId === {{ $section->id }}
                                    ? 'bg-[#0777be] text-white shadow-md'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                                <div class="flex items-center gap-3">
                                    <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full"
                                        :class="currentSectionId === {{ $section->id }} ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="truncate block max-w-[150px] sm:max-w-[200px] lg:max-w-[120px]">{{ $section->name }}</span>
                                </div>
                                <svg x-show="currentSectionId === {{ $section->id }}" class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. RIGHT AREA: Question List --}}
            <div class="lg:col-span-9">
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl min-h-[500px] lg:min-h-[600px] flex flex-col">

                    {{-- Header --}}
                    <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Section Questions</h2>
                            <p class="text-sm text-gray-500">Manage questions for the selected section.</p>
                        </div>
                        <div class="w-full sm:w-auto">
                            <button @click="openBankModal()" class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-[#0777be] text-white text-sm font-bold rounded-lg shadow-md hover:bg-[#0666a3] transition active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Import from Bank
                            </button>
                        </div>
                    </div>

                    {{-- Loading State --}}
                    <div x-show="questions.length === 0 && totalQuestions > 0 && !questionsLoaded" class="flex-1 flex justify-center items-center py-20">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0777be]"></div>
                    </div>

                    {{-- Empty State --}}
                    <div x-show="questions.length === 0" class="flex-1 flex flex-col items-center justify-center p-8 sm:p-12 text-center" style="display: none;">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">No Questions Yet</h3>
                        <p class="text-gray-500 max-w-xs mx-auto mt-1 mb-6 text-sm">This section is empty. Start adding questions.</p>
                        <button @click="openBankModal()" class="text-[#0777be] font-bold text-sm hover:underline">Browse Question Bank &rarr;</button>
                    </div>

                    {{-- Table List (Scrollable on Mobile) --}}
                    <div x-show="questions.length > 0" class="flex-1 w-full overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px] sm:min-w-full">
                            <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="px-4 sm:px-6 py-4 w-3/5">Question Details</th>
                                    <th class="px-4 sm:px-6 py-4 text-center w-1/5">Type</th>
                                    <th class="px-4 sm:px-6 py-4 text-center">Marks</th>
                                    <th class="px-4 sm:px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="q in questions" :key="q.id">
                                    <tr class="hover:bg-blue-50/30 transition group">
                                        <td class="px-4 sm:px-6 py-4 align-top">
                                            <div class="flex items-start gap-3">
                                                <span class="hidden sm:inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-500 font-bold border border-gray-200" x-text="q.id"></span>
                                                <div class="flex-1 min-w-0">
                                                    {{-- HTML Content Safe Render --}}
                                                    <div class="text-sm font-medium text-gray-800 line-clamp-2 prose prose-sm max-w-none" x-html="q.question"></div>

                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide whitespace-nowrap"
                                                            :class="{
                                                                'bg-green-100 text-green-700': q.difficulty === 'Easy' || q.difficulty === 'Very Easy',
                                                                'bg-yellow-100 text-yellow-700': q.difficulty === 'Medium',
                                                                'bg-red-100 text-red-700': q.difficulty === 'Hard' || q.difficulty === 'Very High'
                                                            }" x-text="q.difficulty">
                                                        </span>
                                                        {{-- Mobile Only Type Badge --}}
                                                        <span class="sm:hidden inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-600 border border-gray-200" x-text="q.type_code"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-center hidden sm:table-cell">
                                            <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded-lg border border-gray-200 whitespace-nowrap" x-text="q.type_code"></span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-center">
                                            <span class="font-bold text-gray-700 text-sm" x-text="q.default_marks"></span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 text-right">
                                            <button @click="removeQuestion(q.id)" class="text-gray-400 hover:text-red-500 p-2 rounded-full hover:bg-red-50 transition" title="Remove">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50 rounded-b-xl" x-show="pagination.total > 10">
                        <button @click="changePage(pagination.prev_page_url)" :disabled="!pagination.prev_page_url" class="px-3 py-1.5 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-lg disabled:opacity-50 hover:bg-gray-50 transition">Prev</button>
                        <span class="text-xs font-medium text-gray-500">Page <span x-text="pagination.current_page"></span> / <span x-text="pagination.last_page"></span></span>
                        <button @click="changePage(pagination.next_page_url)" :disabled="!pagination.next_page_url" class="px-3 py-1.5 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-lg disabled:opacity-50 hover:bg-gray-50 transition">Next</button>
                    </div>
                </div>

                {{-- Finish Button --}}
                {{-- <div class="flex justify-end mt-6 pb-10 sm:pb-0">
                    <a href="{{ route('admin.exams.index') }}" class="w-full sm:w-auto justify-center px-8 py-3 bg-gray-800 text-white font-bold rounded-xl shadow-lg hover:bg-gray-900 transition flex items-center gap-2">
                        <span>Save & Finish Exam</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </a>
                </div> --}}
                {{-- Main "Next Step" Button --}}
    @if($exam->examSections->count() > 0)
        <a href="{{ route('admin.exams.schedules.index', $exam->id) }}" class="flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow-md hover:bg-[#0666a3] hover:shadow-lg">
            <span>Next: Add Schedules</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
    @else
        <button disabled class="flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-gray-300 cursor-not-allowed rounded-xl">
            <span>Next: Add Schedules</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
        <p class="mt-2 text-xs text-center text-red-500 w-full md:w-auto">Please add at least one question to proceed.</p>
    @endif
            </div>
        </div>
    </div>

    {{-- 4. MODAL: QUESTION BANK (Responsive) --}}
    <div x-show="showBankModal" style="display: none;" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showBankModal = false"></div>

            <div class="fixed inset-y-0 right-0 pl-0 sm:pl-10 max-w-full flex">

                {{-- Panel --}}
                <div class="w-screen max-w-6xl transform transition-transform bg-white shadow-2xl flex flex-col h-full">

                    {{-- Header --}}
                    <div class="px-4 sm:px-6 py-4 bg-white border-b border-gray-200 flex justify-between items-center shadow-sm z-10 shrink-0">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-800">Question Bank</h3>
                            <p class="hidden sm:block text-xs text-gray-500 mt-0.5">Browse and import questions.</p>
                        </div>
                        <button @click="showBankModal = false" class="p-2 bg-gray-100 rounded-full text-gray-500 hover:bg-gray-200 hover:text-gray-700 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Content Area (Flex Column for Mobile, Row for Desktop) --}}
                    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">

                        {{-- SIDEBAR FILTERS (Collapsible/Top on Mobile) --}}
                        <div class="w-full lg:w-80 bg-gray-50 border-b lg:border-b-0 lg:border-r border-gray-200 flex flex-col shrink-0 max-h-[30vh] lg:max-h-full overflow-y-auto">
                            <div class="p-4 sm:p-5 space-y-4 lg:space-y-6">

                                {{-- Search Input --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Search</label>
                                    <div class="relative">
                                        <input type="text" x-model="bankFilters.search" @input.debounce.500ms="loadBankQuestions()" placeholder="Search text/code..." class="w-full pl-9 pr-3 py-2 text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                </div>

                                {{-- Filters Grid for Mobile --}}
                                <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                                    {{-- Type --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 lg:mb-2">Type</label>
                                        <select x-model="bankFilters.type" @change="loadBankQuestions()" class="w-full text-xs sm:text-sm border-gray-300 rounded-lg focus:ring-[#0777be]">
                                            <option value="">All Types</option>
                                            @foreach($questionTypes as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
                                        </select>
                                    </div>

                                    {{-- Difficulty --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 lg:mb-2">Difficulty</label>
                                        <select x-model="bankFilters.difficulty" @change="loadBankQuestions()" class="w-full text-xs sm:text-sm border-gray-300 rounded-lg focus:ring-[#0777be]">
                                            <option value="">All Levels</option>
                                            @foreach($difficultyLevels as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                                        </select>
                                    </div>

                                    {{-- Topic --}}
                                    <div class="col-span-2 lg:col-span-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 lg:mb-2">Topic</label>
                                        <select x-model="bankFilters.topic" @change="loadBankQuestions()" class="w-full text-xs sm:text-sm border-gray-300 rounded-lg focus:ring-[#0777be]">
                                            <option value="">All Topics</option>
                                            @foreach($topics as $topic) <option value="{{ $topic->id }}">{{ $topic->name }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Reset Button --}}
                            <div class="p-4 mt-auto border-t border-gray-200 bg-white lg:bg-transparent">
                                <button @click="resetFilters()" class="w-full py-2 bg-white lg:bg-gray-100 border border-gray-300 text-gray-600 font-bold text-xs rounded-lg hover:bg-gray-50 transition">
                                    Reset Filters
                                </button>
                            </div>
                        </div>

                        {{-- MAIN LIST --}}
                        <div class="flex-1 flex flex-col bg-white overflow-hidden relative">

                            {{-- Loading Overlay --}}
                            <div x-show="bankLoading" class="absolute inset-0 bg-white/90 z-20 flex flex-col items-center justify-center backdrop-blur-[2px]">
                                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#0777be]"></div>
                                <span class="mt-3 text-sm font-bold text-gray-500 animate-pulse">Loading Questions...</span>
                            </div>

                            {{-- Results Area --}}
                            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                                <template x-for="q in bankQuestions" :key="q.id">
                                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:border-blue-300 transition-all group relative">
                                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">

                                            <div class="flex-1 space-y-2 w-full">
                                                {{-- Badges --}}
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="bg-gray-100 text-gray-600 text-[10px] font-mono px-2 py-0.5 rounded border border-gray-200" x-text="q.code || 'NO-CODE'"></span>
                                                    <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase" x-text="q.question_type?.code"></span>
                                                    <span class="bg-yellow-50 text-yellow-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase" x-text="q.difficulty_level?.name"></span>
                                                </div>

                                                {{-- Question Content --}}
                                                <div class="text-sm text-gray-800 leading-relaxed prose prose-sm max-w-none break-words" x-html="q.question"></div>

                                                {{-- Footer --}}
                                                <div class="pt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400 border-t border-gray-50 mt-2 sm:border-0 sm:mt-0">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                                        <span x-text="q.topic?.name || 'Uncategorized'"></span>
                                                    </span>
                                                    <span class="hidden sm:inline">•</span>
                                                    <span>Marks: <strong class="text-gray-700" x-text="q.default_marks"></strong></span>
                                                </div>
                                            </div>

                                            {{-- Add Button --}}
                                            <button @click="addQuestion(q.id)" class="w-full sm:w-auto shrink-0 flex justify-center items-center gap-1.5 px-4 py-2 bg-white border-2 border-[#0777be] text-[#0777be] text-xs font-bold rounded-lg hover:bg-[#0777be] hover:text-white transition active:scale-95">
                                                <span>Add</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Empty State for Bank --}}
                                <div x-show="!bankLoading && bankQuestions.length === 0" class="flex flex-col items-center justify-center py-10 sm:py-20 text-gray-400 h-full">
                                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <p class="text-sm sm:text-base font-medium text-gray-500">No matching questions found</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                                    <button @click="resetFilters()" class="mt-4 px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50">Clear Filters</button>
                                </div>
                            </div>

                            {{-- Bank Pagination --}}
                            <div class="px-4 sm:px-6 py-3 border-t border-gray-200 bg-white flex justify-between items-center shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] shrink-0" x-show="bankPagination.total > 10">
                                <button @click="changeBankPage(bankPagination.prev_page_url)" :disabled="!bankPagination.prev_page_url" class="px-3 py-1.5 text-xs font-bold bg-white border border-gray-300 rounded-lg disabled:opacity-50 hover:bg-gray-50 transition">Prev</button>
                                <span class="text-xs font-medium text-gray-500">Page <span x-text="bankPagination.current_page"></span> / <span x-text="bankPagination.last_page"></span></span>
                                <button @click="changeBankPage(bankPagination.next_page_url)" :disabled="!bankPagination.next_page_url" class="px-3 py-1.5 text-xs font-bold bg-white border border-gray-300 rounded-lg disabled:opacity-50 hover:bg-gray-50 transition">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function questionManager() {
    return {
        examId: {{ $exam->id }},
        currentSectionId: {{ $examSections->first()->id ?? 'null' }},
        questions: [],
        totalQuestions: 0,
        questionsLoaded: false,
        pagination: {},
        examSections: @json($examSections), // Pass PHP array to JS

        showBankModal: false,
        bankQuestions: [],
        bankLoading: false,
        bankPagination: {},
        bankFilters: { search: '', type: '', difficulty: '', topic: '', skill: '' },

        init() {
            if(this.currentSectionId) this.loadSectionQuestions();
        },

        loadSectionQuestions(url = null) {
            if(!this.currentSectionId) return;
            this.questionsLoaded = false;

            const endpoint = url || `/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions`;

            fetch(endpoint)
                .then(r => r.json())
                .then(data => {
                    this.questions = data.data;
                    this.totalQuestions = data.total;
                    this.pagination = data;
                    this.questionsLoaded = true;
                });
        },

        changePage(url) { if(url) this.loadSectionQuestions(url); },

        removeQuestion(id) {
            if(!confirm('Remove this question from the section?')) return;

            fetch(`/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/remove`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') this.loadSectionQuestions();
                else alert(data.message);
            });
        },

        openBankModal() {
            this.showBankModal = true;
            this.resetFilters();
        },

        loadBankQuestions(url = null) {
            this.bankLoading = true;
            const params = new URLSearchParams();
            Object.keys(this.bankFilters).forEach(key => {
                if (this.bankFilters[key]) params.append(key, this.bankFilters[key]);
            });

            const baseUrl = `/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/available`;
            const finalUrl = (url || baseUrl) + '?' + params.toString();

            fetch(finalUrl)
                .then(r => r.json())
                .then(data => {
                    this.bankQuestions = data.data;
                    this.bankPagination = data;
                    this.bankLoading = false;
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    this.bankLoading = false;
                });
        },

        resetFilters() {
            this.bankFilters = { search: '', type: '', difficulty: '', topic: '', skill: '' };
            this.loadBankQuestions();
        },

        changeBankPage(url) { if(url) this.loadBankQuestions(url); },

        addQuestion(id) {
            fetch(`/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.loadSectionQuestions();
                    this.bankQuestions = this.bankQuestions.filter(q => q.id !== id);
                } else {
                    alert(data.message);
                }
            });
        }
    }
}
</script>
@endsection
