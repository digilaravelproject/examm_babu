@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50/50" x-data="questionManager()">

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
            @include('admin.exams.partials._steps', ['activeStep' => 'questions'])
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
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Section Questions</h2>
                        </div>
                        <div class="flex items-center w-full gap-3 sm:w-auto">
                            <select x-model="perPage" @change="loadSectionQuestions()" class="text-xs border-gray-300 rounded-lg py-1.5 focus:ring-[#0777be]">
                                <option value="10">10 Rows</option>
                                <option value="50">50 Rows</option>
                                <option value="100">100 Rows</option>
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
                                    <th class="w-3/5 px-6 py-4">Question Detail</th>
                                    <th class="px-6 py-4 text-center">Type</th>
                                    <th class="px-6 py-4 text-center">Marks</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="q in questions" :key="q.id">
                                    <tr class="transition hover:bg-blue-50/30">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium prose-sm text-gray-800 line-clamp-2 max-w-none" x-html="q.question"></div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-500 rounded border border-gray-200" x-text="q.type_code"></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-center text-gray-700" x-text="q.default_marks"></td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="removeQuestion(q.id)" class="p-2 text-gray-300 transition rounded-full hover:text-red-500 hover:bg-red-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
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
                        <a href="{{ route('admin.exams.schedules.index', $exam->id) }}" class="inline-flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow-lg hover:bg-[#0666a3]">
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
                    <h3 class="text-lg font-bold tracking-wider text-gray-800 uppercase">Question Bank</h3>
                    <button @click="showBankModal = false" class="p-2 text-gray-400 transition bg-gray-100 rounded-full hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-col flex-1 overflow-hidden lg:flex-row">
                    {{-- Bank Sidebar Filters --}}
                    <div class="w-full p-5 space-y-5 overflow-y-auto border-b border-gray-200 lg:w-72 bg-gray-50 lg:border-r shrink-0">
                        <div>
                            <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Search</label>
                            <input type="text" x-model="bankFilters.search" @input.debounce.500ms="loadBankQuestions()" placeholder="Code or text..." class="w-full px-3 py-2 text-sm border-gray-300 rounded-lg focus:ring-[#0777be]">
                        </div>
                        <div class="space-y-4">
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
                                <label class="block mb-1 text-[11px] font-black text-gray-400 uppercase">Show Per Page</label>
                                <select x-model="bankPerPage" @change="loadBankQuestions()" class="w-full py-2 text-xs border-gray-300 rounded-lg">
                                    <option value="10">10 Questions</option>
                                    <option value="50">50 Questions</option>
                                    <option value="100">100 Questions</option>
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
                            <template x-for="q in bankQuestions" :key="q.id">
                                {{-- ANTI-1062 HIDE LOGIC --}}
                                <div x-show="!questionsInExam.includes(q.id)"
                                     class="flex items-center justify-between gap-4 p-4 transition bg-white border border-gray-100 shadow-sm rounded-xl hover:border-blue-300">
                                    <div class="flex-1 min-w-0">
                                        <div class="mb-1.5 flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-bold text-[#0777be] bg-blue-50 px-1.5 rounded" x-text="q.code"></span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase" x-text="q.difficulty_level?.name"></span>
                                        </div>
                                        <div class="text-sm prose-sm text-gray-700 line-clamp-2" x-html="q.question"></div>
                                    </div>
                                    <button @click="addQuestion(q.id)"
                                            :disabled="addingIds.includes(q.id)"
                                            class="shrink-0 px-6 py-2 bg-[#0777be] text-white text-[11px] font-black rounded-lg hover:bg-[#0666a3] disabled:opacity-50 transition active:scale-95 shadow-sm">
                                        <span x-text="addingIds.includes(q.id) ? 'Adding...' : 'Add to Exam'"></span>
                                    </button>
                                </div>
                            </template>

                            <div x-show="!bankLoading && bankQuestions.filter(q => !questionsInExam.includes(q.id)).length === 0" class="py-20 italic text-center text-gray-400">
                                No more questions available for this filter.
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
</div>

<script>
function questionManager() {
    return {
        examId: {{ $exam->id }},
        currentSectionId: {{ $examSections->first()->id ?? 'null' }},
        questions: [],
        questionsInExam: [], // List of all question IDs in entire exam
        totalExamQuestionsCount: 0,
        questionsLoaded: false,
        pagination: {},
        perPage: 10,

        showBankModal: false,
        bankQuestions: [],
        bankLoading: false,
        bankPagination: {},
        bankPerPage: 10,
        bankFilters: { search: '', type: '', difficulty: '', topic: '' },
        addingIds: [],
        toasts: [],

        init() {
            this.fetchGlobalExamStatus();
            if(this.currentSectionId) this.loadSectionQuestions();
        },

        // Fetch all IDs in the whole exam across all sections
        fetchGlobalExamStatus() {
            fetch(`/admin/exams/${this.examId}/all-question-ids`)
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

        loadSectionQuestions(url = null) {
            if(!this.currentSectionId) return;
            this.questionsLoaded = false;
            const fetchUrl = new URL(url || `/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions`, window.location.origin);
            fetchUrl.searchParams.append('per_page', this.perPage);

            fetch(fetchUrl).then(r => r.json()).then(data => {
                this.questions = data.data;
                this.pagination = data;
                this.questionsLoaded = true;
            });
        },

        loadBankQuestions(url = null) {
            this.bankLoading = true;
            let baseUrl = url || `/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/available`;
            let fetchUrl = new URL(baseUrl, window.location.origin);

            fetchUrl.searchParams.append('per_page', this.bankPerPage);
            Object.keys(this.bankFilters).forEach(key => {
                if (this.bankFilters[key]) fetchUrl.searchParams.append(key, this.bankFilters[key]);
            });

            fetch(fetchUrl.toString()).then(r => r.json()).then(data => {
                this.bankQuestions = data.data;
                this.bankPagination = data;
                this.bankLoading = false;
            }).catch(() => this.bankLoading = false);
        },

        addQuestion(id) {
            // Check locally to stop 1062 early
            if(this.addingIds.includes(id) || this.questionsInExam.includes(id)) return;
            this.addingIds.push(id);

            fetch(`/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.addToast("Question Added to Section!");
                    this.questionsInExam.push(id); // Block globally
                    this.totalExamQuestionsCount++;
                    this.loadSectionQuestions();
                    // Refill list logic
                    this.loadBankQuestions(this.bankPagination.path + '?page=' + this.bankPagination.current_page);
                } else {
                    this.addToast(data.message, 'error');
                }
            }).finally(() => {
                this.addingIds = this.addingIds.filter(i => i !== id);
            });
        },

        removeQuestion(id) {
            if(!confirm('Remove this question?')) return;
            fetch(`/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/remove`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.addToast("Question Removed!", "error");
                    this.questionsInExam = this.questionsInExam.filter(i => i !== id);
                    this.totalExamQuestionsCount--;
                    this.loadSectionQuestions();
                }
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
