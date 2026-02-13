@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('content')

@php
    // --- Dynamic Route Logic ---
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $urlPrefix = $isAdmin ? 'admin' : 'instructor'; // For JS fetch calls

    // Prepare Parameters
    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }
@endphp

<div class="min-h-screen bg-gray-50/50" x-data="questionManager({
    examId: {{ $exam->id }},
    urlPrefix: '{{ $urlPrefix }}',
    currentSectionId: {{ $examSections->first()->id ?? 'null' }}
})">

    {{-- Notification Toasts --}}
    <div class="fixed top-5 right-5 z-[9999] space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition.duration.300ms class="flex items-center gap-3 px-5 py-3 rounded-lg shadow-2xl text-white text-sm font-bold min-w-[280px]"
                 :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'">
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <div class="max-w-[1400px] mx-auto py-4 px-4 sm:px-6 lg:px-8">

        {{-- 1. Wizard Steps --}}
        <div class="pb-2 mb-6 overflow-x-auto lg:mb-8">
            @include('admin.exams.partials._steps', ['activeStep' => 'questions', 'routePrefix' => $routePrefix, 'routeParams' => $routeParams])
        </div>

        <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- 2. LEFT SIDEBAR --}}
            <div class="space-y-4 lg:col-span-3">

                {{-- Stats Card --}}
                <div class="bg-gradient-to-br from-[#0777be] to-[#0666a3] rounded-xl shadow-lg p-5 text-white flex justify-between items-center lg:block">
                    <div>
                        <p class="mb-1 text-xs font-medium tracking-widest text-blue-100 uppercase">Total Added</p>
                        <div class="flex items-baseline gap-1">
                            <h2 class="text-3xl font-extrabold" x-text="totalExamQuestionsCount">0</h2>
                            <span class="text-xs text-blue-200">Questions</span>
                        </div>
                    </div>
                </div>

                {{-- Section Selector --}}
                <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl lg:sticky lg:top-6">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-xs font-bold tracking-wider text-gray-500 uppercase">Exam Sections</h3>
                    </div>

                    <div class="p-2 space-y-1 overflow-y-auto max-h-48 lg:max-h-[60vh]">
                        @foreach($examSections as $section)
                            <button @click="currentSectionId = {{ $section->id }}; loadSectionQuestions()"
                                class="flex items-center justify-between w-full px-4 py-3 text-sm font-semibold text-left transition-all rounded-lg group"
                                :class="currentSectionId === {{ $section->id }} ? 'bg-[#0777be] text-white shadow-md' : 'text-gray-600 hover:bg-gray-50'">
                                <div class="flex items-center gap-3 truncate">
                                    <span class="flex items-center justify-center flex-shrink-0 w-6 h-6 text-[10px] font-bold rounded-full"
                                        :class="currentSectionId === {{ $section->id }} ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="truncate">{{ $section->name }}</span>
                                </div>
                                <svg x-show="currentSectionId === {{ $section->id }}" class="flex-shrink-0 w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. RIGHT AREA --}}
            <div class="lg:col-span-9">
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl min-h-[600px] flex flex-col overflow-hidden">

                    {{-- Main Header --}}
                    <div class="flex flex-col items-start justify-between gap-4 px-6 py-4 bg-white border-b border-gray-100 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-gray-800">Section Questions</h2>
                            {{-- 🔥 BULK DELETE BUTTON --}}
                            <button x-show="selectedQuestions.length > 0" @click="bulkRemove()" x-transition
                                class="px-3 py-1.5 text-xs font-bold text-white bg-red-500 rounded-lg shadow hover:bg-red-600">
                                Delete Selected (<span x-text="selectedQuestions.length"></span>)
                            </button>
                        </div>
                        <div class="flex items-center w-full gap-3 sm:w-auto">
                            {{-- 🔥 PAGINATION OPTIONS --}}
                            <select x-model="perPage" @change="loadSectionQuestions()" class="text-xs border-gray-300 rounded-lg py-1.5 focus:ring-[#0777be]">
                                <option value="10">10 Rows</option>
                                <option value="50">50 Rows</option>
                                <option value="100">100 Rows</option>
                                <option value="300">300 Rows</option>
                                <option value="500">500 Rows</option>
                            </select>
                            <button @click="openBankModal()" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 bg-[#0777be] text-white text-xs font-bold rounded-lg shadow-md hover:bg-[#0666a3] transition active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Import from Bank
                            </button>
                        </div>
                    </div>

                    <div x-show="!questionsLoaded" class="flex items-center justify-center flex-1 py-20">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0777be]"></div>
                    </div>

                    {{-- Questions Table --}}
                    <div x-show="questionsLoaded" class="flex-1 w-full overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="text-xs font-bold text-gray-400 uppercase border-b bg-gray-50/50">
                                <tr>
                                    {{-- 🔥 CHECKBOX HEADER --}}
                                    <th class="w-10 px-6 py-4">
                                        <input type="checkbox" @change="toggleAllQuestions($event)" class="rounded border-gray-300 text-[#0777be] focus:ring-[#0777be]">
                                    </th>
                                    <th class="w-3/5 px-6 py-4">Question Detail</th>
                                    <th class="px-6 py-4 text-center">Type</th>
                                    <th class="px-6 py-4 text-center">Marks</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="q in questions" :key="q.id">
                                    <tr class="transition hover:bg-blue-50/30" :class="selectedQuestions.includes(q.id) ? 'bg-blue-50' : ''">
                                        {{-- 🔥 CHECKBOX ROW --}}
                                        <td class="px-6 py-4">
                                            <input type="checkbox" :value="q.id" x-model="selectedQuestions" class="rounded border-gray-300 text-[#0777be] focus:ring-[#0777be]">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="mb-1">
                                                {{-- 🔥 COMPREHENSION TAG --}}
                                                <template x-if="q.has_attachment && q.attachment_type === 'comprehension'">
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 bg-purple-50 border border-purple-100 rounded">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                        <span>Comprehension</span>
                                                        <span x-show="q.topic_name" class="text-purple-400">:</span>
                                                        <span x-show="q.topic_name" x-text="q.topic_name"></span>
                                                    </span>
                                                </template>
                                            </div>
                                            <div class="text-sm font-medium prose-sm text-gray-800 line-clamp-2 max-w-none" x-html="q.question"></div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-500 rounded border border-gray-200" x-text="q.type_code"></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-center text-gray-700" x-text="q.default_marks"></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openPreview(q.id)" class="p-2 text-gray-400 transition rounded-full hover:text-[#0777be] hover:bg-blue-50" title="Preview Question">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </button>
                                                <button @click="removeQuestion(q.id)" class="p-2 text-gray-300 transition rounded-full hover:text-red-500 hover:bg-red-50" title="Remove Question">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <div x-show="questions.length === 0" class="py-20 text-sm italic text-center text-gray-400">
                            No questions added to this section yet.
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50" x-show="pagination.last_page > 1">
                        <button @click="changePage(pagination.prev_page_url)" :disabled="!pagination.prev_page_url" class="px-4 py-1.5 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-lg disabled:opacity-50">Prev</button>
                        <span class="text-xs font-medium text-gray-500" x-text="`Page ${pagination.current_page} of ${pagination.last_page}`"></span>
                        <button @click="changePage(pagination.next_page_url)" :disabled="!pagination.next_page_url" class="px-4 py-1.5 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-lg disabled:opacity-50">Next</button>
                    </div>
                </div>

                <div class="mt-6">
                    @if($exam->examSections->count() > 0)
                        {{-- Fixed Dynamic Route --}}
                        <a href="{{ route($routePrefix . 'exams.schedules.index', array_merge($routeParams, ['exam' => $exam->id])) }}" class="inline-flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow-lg hover:bg-[#0666a3]">
                            <span>Next: Add Schedules</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MODAL: QUESTION BANK --}}
    <div x-show="showBankModal" style="display: none;" class="fixed inset-0 z-50 overflow-hidden" x-cloak>
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showBankModal = false"></div>
        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="flex flex-col w-screen h-full max-w-5xl transition-transform transform bg-white shadow-2xl">

                <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 shadow-sm shrink-0">
                    <div class="flex items-center gap-4">
                        <h3 class="text-lg font-bold tracking-wider text-gray-800 uppercase">Question Bank</h3>
                        {{-- 🔥 BULK ADD BUTTON --}}
                        <button x-show="selectedBankQuestions.length > 0" @click="bulkAdd()" x-transition
                            class="px-4 py-2 text-xs font-bold text-white bg-green-600 rounded-lg shadow hover:bg-green-700">
                            Add Selected (<span x-text="selectedBankQuestions.length"></span>)
                        </button>
                    </div>
                    <button @click="showBankModal = false" class="p-2 text-gray-400 transition bg-gray-100 rounded-full hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-col flex-1 overflow-hidden lg:flex-row">
                    {{-- Bank Sidebar Filters --}}
                    <div class="w-full p-5 space-y-5 overflow-y-auto border-b border-gray-200 lg:w-72 bg-gray-50 lg:border-r shrink-0">
                        {{-- 🔥 Filter Mode Toggles --}}
                        <div class="flex p-1 mb-4 space-x-1 rounded-lg bg-gray-200/50">
                            <button @click="filterMode = 'all'; loadBankQuestions()"
                                class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all"
                                :class="filterMode === 'all' ? 'bg-white text-[#0777be] shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                All
                            </button>
                            <button @click="filterMode = 'comprehension'; loadBankQuestions()"
                                class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all"
                                :class="filterMode === 'comprehension' ? 'bg-white text-[#0777be] shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                Comprehension
                            </button>
                        </div>

                        <div>
                            <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Search</label>
                            <input type="text" x-model="bankFilters.search" @input.debounce.500ms="loadBankQuestions()" placeholder="Code or text..." class="w-full px-3 py-2 text-sm border-gray-300 rounded-lg focus:ring-[#0777be]">
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Subjects</label>
                                <select x-model="bankFilters.skill" @change="bankFilters.topic=''; loadBankQuestions()" class="w-full py-2 text-xs border-gray-300 rounded-lg">
                                    <option value="">All Subjects</option>
                                    <template x-for="s in skills" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                            {{-- Removed Duplicate Question Type Filter (Code-based) --}}
                            <div>
                                <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Type</label>
                                <select x-model="bankFilters.type" @change="loadBankQuestions()" class="w-full py-2 text-xs border-gray-300 rounded-lg">
                                    <option value="">All Types</option>
                                    @foreach($questionTypes as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Difficulty</label>
                                <select x-model="bankFilters.difficulty" @change="loadBankQuestions()" class="w-full py-2 text-xs border-gray-300 rounded-lg">
                                    <option value="">All Levels</option>
                                    @foreach($difficultyLevels as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Topic</label>
                                <select x-model="bankFilters.topic" @change="loadBankQuestions()" class="w-full py-2 text-xs border-gray-300 rounded-lg">
                                    <option value="">All Topics</option>
                                    <template x-for="t in filteredTopics()" :key="t.id">
                                        <option :value="t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Show Per Page</label>
                                <select x-model="bankPerPage" @change="loadBankQuestions()" class="w-full py-2 text-xs border-gray-300 rounded-lg">
                                    <option value="10">10 Questions</option>
                                    <option value="50">50 Questions</option>
                                    <option value="100">100 Questions</option>
                                    <option value="300">300 Questions</option>
                                    <option value="500">500 Questions</option>
                                </select>
                            </div>
                        </div>
                        <button @click="resetFilters()" class="w-full py-2 text-xs font-bold tracking-widest text-gray-600 uppercase transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Reset All</button>
                    </div>

                    {{-- Main Bank List --}}
                    <div class="relative flex flex-col flex-1 overflow-hidden bg-white">
                        <div x-show="bankLoading" class="absolute inset-0 z-20 flex items-center justify-center bg-white/80">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0777be]"></div>
                        </div>

                        <div class="flex-1 p-6 space-y-4 overflow-y-auto">
                            {{-- 🔥 SELECT ALL CHECKBOX --}}
                            <div class="flex items-center gap-3 p-2 mb-2 border border-gray-100 rounded bg-gray-50" x-show="bankQuestions.length > 0">
                                <input type="checkbox" @change="toggleAllBankQuestions($event)" class="rounded border-gray-300 text-[#0777be] focus:ring-[#0777be]">
                                <span class="text-xs font-bold text-gray-500">Select All Visible</span>
                            </div>

                            <template x-for="q in bankQuestions" :key="q.id">
                                <div x-show="!questionsInExam.includes(q.id)"
                                     class="flex items-center justify-between gap-4 p-4 transition bg-white border border-gray-100 shadow-sm rounded-xl hover:border-blue-300"
                                     :class="selectedBankQuestions.includes(q.id) ? 'bg-green-50 border-green-200' : ''">

                                    {{-- 🔥 CHECKBOX ROW --}}
                                    <div class="shrink-0">
                                        <input type="checkbox" :value="q.id" x-model="selectedBankQuestions" class="rounded border-gray-300 text-[#0777be] focus:ring-[#0777be]">
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                            <span class="text-[10px] font-mono font-bold text-[#0777be] bg-blue-50 px-1.5 rounded" x-text="q.code"></span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase" x-text="q.difficulty_level?.name"></span>

                                            {{-- 🔥 COMPREHENSION TAG --}}
                                            <template x-if="q.has_attachment && q.attachment_type === 'comprehension'">
                                                <span class="flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 bg-purple-50 border border-purple-100 rounded">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                                    <span>Comprehension</span>
                                                    <span x-show="q.topic" class="text-purple-400">:</span>
                                                    <span x-show="q.topic" x-text="q.topic?.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <div class="text-sm prose-sm text-gray-700 line-clamp-2" x-html="q.question"></div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <button @click="openPreview(q.id)" class="p-2 text-gray-400 transition bg-gray-50 rounded-lg hover:text-[#0777be] hover:bg-blue-50 border border-gray-200" title="Preview">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        {{-- Add Single Button --}}
                                        <button @click="addQuestion(q.id)"
                                            :disabled="addingIds.includes(q.id)"
                                            class="px-4 py-2 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg hover:bg-gray-200 disabled:opacity-50 transition active:scale-95">
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="!bankLoading && bankQuestions.filter(q => !questionsInExam.includes(q.id)).length === 0" class="py-20 italic text-center text-gray-400">
                                No more questions available.
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-white border-t border-gray-200 flex justify-between items-center shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]" x-show="bankPagination.last_page > 1">
                            <button @click="changeBankPage(bankPagination.prev_page_url)" :disabled="!bankPagination.prev_page_url" class="px-4 py-1.5 text-xs font-bold bg-white border border-gray-300 rounded-lg">Prev</button>
                            <span class="text-xs font-medium text-gray-500" x-text="`Page ${bankPagination.current_page} of ${bankPagination.last_page}`"></span>
                            <button @click="changeBankPage(bankPagination.next_page_url)" :disabled="!bankPagination.next_page_url" class="px-4 py-1.5 text-xs font-bold bg-white border border-gray-300 rounded-lg">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. PREVIEW MODAL --}}
    <div x-show="previewOpen" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div x-show="previewOpen" class="fixed inset-0 transition-opacity" @click="previewOpen = false">
                <div class="absolute inset-0 bg-gray-900 opacity-75 backdrop-blur-sm"></div>
            </div>
            <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-16">
                <div id="preview-content" class="min-h-[200px] bg-gray-50 flex items-center justify-center"></div>
                <div class="flex justify-end px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <button @click="previewOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function questionManager(config) {
    return {
        examId: config.examId,
        urlPrefix: config.urlPrefix,
        currentSectionId: config.currentSectionId,
        questions: [],
        questionsInExam: [],
        totalExamQuestionsCount: 0,
        questionsLoaded: false,
        pagination: {},
        perPage: 10,

        // Selection State
        selectedQuestions: [],
        selectedBankQuestions: [],

        showBankModal: false,
        bankQuestions: [],
        bankLoading: false,
        bankPagination: {},
        bankPerPage: 10,
        bankFilters: { search: '', type: '', difficulty: '', topic: '', skill: '' },
        filterMode: 'all', // 'all' or 'comprehension'
        addingIds: [],
        toasts: [],
        previewOpen: false,

        // Client-side lists (populated from server)
        skills: @json($skills ?? []),
        allTopics: @json($topics ?? []),

        init() {
            this.fetchGlobalExamStatus();
            if(this.currentSectionId) this.loadSectionQuestions();
        },

        fetchGlobalExamStatus() {
            // FIX: Dynamic URL
            fetch(`/${this.urlPrefix}/exams/${this.examId}/all-question-ids`)
                .then(r => r.json())
                .then(ids => {
                    this.questionsInExam = ids;
                    this.totalExamQuestionsCount = ids.length;
                });
        },

        addToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3000);
        },

        // 🔥 LOAD SECTION QUESTIONS (Reset Selection)
        loadSectionQuestions(url = null) {
            if(!this.currentSectionId) return;
            this.questionsLoaded = false;
            this.selectedQuestions = []; // Reset checkboxes

            // FIX: Dynamic URL
            let baseUrl = url || `/${this.urlPrefix}/exams/${this.examId}/sections/${this.currentSectionId}/questions`;

            // Note: If url passed (pagination), it is full string. If constructing, use origin.
            // Simplified approach:
            const fetchUrl = new URL(baseUrl, window.location.origin);
            if(!url) fetchUrl.searchParams.append('per_page', this.perPage);

            fetch(fetchUrl).then(r => r.json()).then(data => {
                this.questions = data.data;
                this.pagination = data;
                this.questionsLoaded = true;
            });
        },

        // 🔥 LOAD BANK QUESTIONS (Reset Selection)
        loadBankQuestions(url = null) {
            this.bankLoading = true;
            this.selectedBankQuestions = []; // Reset checkboxes
            // FIX: Dynamic URL
            let baseUrl = url || `/${this.urlPrefix}/exams/${this.examId}/sections/${this.currentSectionId}/questions/available`;
            let fetchUrl = new URL(baseUrl, window.location.origin);

            if(!url) fetchUrl.searchParams.append('per_page', this.bankPerPage);

            // Add Filter Mode
            if(this.filterMode === 'comprehension') {
                fetchUrl.searchParams.append('is_comprehension', 1);
            }

            Object.keys(this.bankFilters).forEach(key => {
                if (this.bankFilters[key]) fetchUrl.searchParams.append(key, this.bankFilters[key]);
            });

            fetch(fetchUrl.toString()).then(r => r.json()).then(data => {
                this.bankQuestions = data.data;
                this.bankPagination = data;
                this.bankLoading = false;
            }).catch(() => this.bankLoading = false);
        },

        // Return topics filtered by selected skill (or all if none)
        filteredTopics() {
            if(!this.bankFilters.skill) return this.allTopics;
            return this.allTopics.filter(t => String(t.skill_id) === String(this.bankFilters.skill));
        },

        // Toggle All Logic
        toggleAllQuestions(e) {
            this.selectedQuestions = e.target.checked ? this.questions.map(q => q.id) : [];
        },
        toggleAllBankQuestions(e) {
            // Only select visible ones that aren't already in exam
            const available = this.bankQuestions.filter(q => !this.questionsInExam.includes(q.id));
            this.selectedBankQuestions = e.target.checked ? available.map(q => q.id) : [];
        },

        // 🔥 BULK REMOVE LOGIC
        bulkRemove() {
            if(this.selectedQuestions.length === 0) return;
            if(!confirm(`Remove ${this.selectedQuestions.length} selected questions?`)) return;

            // FIX: Dynamic URL
            fetch(`/${this.urlPrefix}/exams/${this.examId}/sections/${this.currentSectionId}/questions/remove`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_ids: this.selectedQuestions })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.addToast("Questions Removed!");
                    // Remove from Global List
                    this.questionsInExam = this.questionsInExam.filter(id => !this.selectedQuestions.includes(id));
                    this.totalExamQuestionsCount = this.questionsInExam.length;

                    this.loadSectionQuestions(); // Refresh UI
                } else {
                    this.addToast("Error Removing", 'error');
                }
            });
        },

        // 🔥 BULK ADD LOGIC
        bulkAdd() {
            if(this.selectedBankQuestions.length === 0) return;
            const idsToAdd = this.selectedBankQuestions;

            // FIX: Dynamic URL
            fetch(`/${this.urlPrefix}/exams/${this.examId}/sections/${this.currentSectionId}/questions/add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_ids: idsToAdd })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.addToast(data.message);

                    // Update global state
                    idsToAdd.forEach(id => {
                        if(!this.questionsInExam.includes(id)) this.questionsInExam.push(id);
                    });
                    this.totalExamQuestionsCount = this.questionsInExam.length;

                    // Refresh Section List
                    this.loadSectionQuestions();

                    // Refresh Bank List (to hide added ones)
                    this.selectedBankQuestions = [];
                    // Handle pagination refresh properly
                    const currentUrl = this.bankPagination.path + '?page=' + this.bankPagination.current_page;
                    this.loadBankQuestions(currentUrl);
                }
            });
        },

        // Keep Single Actions for compatibility
        addQuestion(id) {
            this.selectedBankQuestions = [id];
            this.bulkAdd();
        },
        removeQuestion(id) {
            this.selectedQuestions = [id];
            this.bulkRemove();
        },

        openPreview(id) {
            this.previewOpen = true;
            document.getElementById('preview-content').innerHTML = '<div class="flex justify-center p-10"><svg class="w-8 h-8 text-[#0777be] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';

            // FIX: Dynamic URL
            fetch(`/${this.urlPrefix}/questions/${id}/preview`).then(r => r.text()).then(html => {
                document.getElementById('preview-content').innerHTML = html;
            });
        },

        changePage(url) { if(url) this.loadSectionQuestions(url); },
        openBankModal() { this.showBankModal = true; this.loadBankQuestions(); },
        changeBankPage(url) { if(url) this.loadBankQuestions(url); },
        resetFilters() { this.bankFilters = { search: '', type: '', difficulty: '', topic: '' }; this.loadBankQuestions(); }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
