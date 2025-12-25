@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50/50" x-data="questionManager()">

    {{-- Toast Notifications --}}
    <div class="fixed z-[9999] top-5 right-5 space-y-3">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition class="flex items-center gap-3 px-4 py-3 text-white rounded-lg shadow-2xl min-w-[300px]"
                 :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'">
                <span x-text="toast.message" class="text-sm font-bold"></span>
            </div>
        </template>
    </div>

    <div class="max-w-[1400px] mx-auto py-3 px-4 sm:px-6 lg:px-8">

        {{-- 1. Wizard Steps (Condensed) --}}
        <div class="mb-4 overflow-x-auto">
            @include('admin.exams.partials._steps', ['activeStep' => 'questions'])
        </div>

        <div class="grid items-start grid-cols-1 gap-4 lg:grid-cols-12">

            {{-- 2. LEFT SIDEBAR --}}
            <div class="space-y-3 lg:col-span-3">
                <div class="bg-gradient-to-br from-[#0777be] to-[#0666a3] rounded-xl shadow-md p-4 text-white flex justify-between items-center lg:block">
                    <div>
                        <p class="text-[10px] font-bold text-blue-100 uppercase">Total Questions</p>
                        <h2 class="text-2xl font-black" x-text="totalQuestions">0</h2>
                    </div>
                </div>

                <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl lg:sticky lg:top-4">
                    <div class="px-3 py-2 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-[10px] font-bold text-gray-500 uppercase">Sections</h3>
                    </div>
                    <div class="p-1.5 space-y-1 max-h-48 lg:max-h-[60vh] overflow-y-auto">
                        @foreach($examSections as $section)
                            <button @click="currentSectionId = {{ $section->id }}; loadSectionQuestions()"
                                class="flex items-center justify-between w-full px-3 py-2 text-xs font-semibold text-left transition-all rounded-lg"
                                :class="currentSectionId === {{ $section->id }} ? 'bg-[#0777be] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
                                <span class="truncate">{{ $section->name }}</span>
                                <svg x-show="currentSectionId === {{ $section->id }}" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. RIGHT AREA --}}
            <div class="lg:col-span-9">
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl min-h-[500px] flex flex-col">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-bold text-gray-800">Questions List</h2>
                        <button @click="openBankModal()" class="flex items-center gap-2 px-4 py-1.5 bg-[#0777be] text-white text-xs font-bold rounded-lg hover:bg-[#0666a3] transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Questions
                        </button>
                    </div>

                    {{-- Table --}}
                    <div x-show="questions.length > 0" class="flex-1 w-full overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="text-[10px] font-bold text-gray-400 uppercase bg-gray-50/50 border-b">
                                <tr>
                                    <th class="w-3/5 px-4 py-2">Question Details</th>
                                    <th class="px-4 py-2 text-center">Type</th>
                                    <th class="px-4 py-2 text-center">Marks</th>
                                    <th class="px-4 py-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="q in questions" :key="q.id">
                                    <tr class="hover:bg-blue-50/20">
                                        <td class="px-4 py-2.5">
                                            <div class="text-xs font-medium prose-sm text-gray-800 line-clamp-1" x-html="q.question"></div>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <span class="text-[10px] font-bold bg-gray-100 px-1.5 py-0.5 rounded text-gray-500" x-text="q.type_code"></span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-bold text-xs" x-text="q.default_marks"></td>
                                        <td class="px-4 py-2.5 text-right">
                                            <button @click="removeQuestion(q.id)" class="text-gray-400 transition hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    @if($exam->examSections->count() > 0)
                        <a href="{{ route('admin.exams.schedules.index', $exam->id) }}" class="inline-flex items-center gap-2 px-8 py-2.5 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow hover:bg-[#0666a3]">
                            <span>Next: Add Schedules</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MODAL: QUESTION BANK (Condensed UI) --}}
    <div x-show="showBankModal" style="display: none;" class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showBankModal = false"></div>
        <div class="fixed inset-y-0 right-0 flex max-w-full">
            <div class="flex flex-col w-screen h-full max-w-5xl transition-transform transform bg-white shadow-2xl">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200 shadow-sm shrink-0">
                    <h3 class="text-sm font-black tracking-widest text-gray-800 uppercase">Question Bank</h3>
                    <button @click="showBankModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-col flex-1 overflow-hidden lg:flex-row">
                    {{-- Filters Sidebar (Compact) --}}
                    <div class="w-full p-3 space-y-3 overflow-y-auto border-b border-gray-200 lg:w-64 bg-gray-50 lg:border-r shrink-0">
                        <div>
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Search</label>
                            <input type="text" x-model="bankFilters.search" @input.debounce.500ms="loadBankQuestions()" placeholder="Type here..." class="w-full px-2 py-1.5 text-xs border-gray-300 rounded-lg focus:ring-[#0777be]">
                        </div>
                        <div class="space-y-2">
                            <div>
                                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Type</label>
                                <select x-model="bankFilters.type" @change="loadBankQuestions()" class="w-full py-1 text-xs border-gray-300 rounded-lg focus:ring-[#0777be]">
                                    <option value="">All Types</option>
                                    @foreach($questionTypes as $t) <option value="{{ $t->id }}">{{ $t->name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase">Difficulty</label>
                                <select x-model="bankFilters.difficulty" @change="loadBankQuestions()" class="w-full py-1 text-xs border-gray-300 rounded-lg focus:ring-[#0777be]">
                                    <option value="">All Levels</option>
                                    @foreach($difficultyLevels as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <button @click="resetFilters()" class="w-full py-1.5 bg-white border border-gray-200 text-gray-500 font-bold text-[10px] rounded hover:bg-gray-100 transition">RESET FILTERS</button>
                    </div>

                    {{-- Main Bank List --}}
                    <div class="relative flex flex-col flex-1 overflow-hidden bg-white">
                        <div x-show="bankLoading" class="absolute inset-0 z-20 flex items-center justify-center bg-white/80">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#0777be]"></div>
                        </div>

                        <div class="flex-1 p-3 space-y-2 overflow-y-auto">
                            <template x-for="q in bankQuestions" :key="q.id">
                                <div class="p-2.5 transition bg-white border border-gray-100 rounded-xl hover:border-blue-200 shadow-sm flex items-center justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[9px] font-mono font-bold text-[#0777be]" x-text="q.code"></span>
                                            <span class="text-[9px] font-bold text-gray-400 uppercase" x-text="q.difficulty_level?.name"></span>
                                        </div>
                                        <div class="text-xs prose-sm text-gray-700 line-clamp-2" x-html="q.question"></div>
                                    </div>
                                    <button @click="addQuestion(q.id)"
                                            :disabled="addingIds.includes(q.id)"
                                            class="shrink-0 px-4 py-1.5 bg-[#0777be] text-white text-[10px] font-black rounded-lg hover:bg-[#0666a3] disabled:opacity-50 transition">
                                        <span x-text="addingIds.includes(q.id) ? 'ADDING...' : 'ADD'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Modal Pagination --}}
                        <div class="flex items-center justify-between px-4 py-2 bg-white border-t border-gray-100" x-show="bankPagination.last_page > 1">
                            <button @click="changeBankPage(bankPagination.prev_page_url)" :disabled="!bankPagination.prev_page_url" class="px-2 py-1 text-[10px] font-bold bg-white border border-gray-200 rounded disabled:opacity-50">Prev</button>
                            <span class="text-[10px] font-medium text-gray-500" x-text="`Page ${bankPagination.current_page} / ${bankPagination.last_page}`"></span>
                            <button @click="changeBankPage(bankPagination.next_page_url)" :disabled="!bankPagination.next_page_url" class="px-2 py-1 text-[10px] font-bold bg-white border border-gray-200 rounded disabled:opacity-50">Next</button>
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
        examSections: @json($examSections),

        showBankModal: false,
        bankQuestions: [],
        bankLoading: false,
        bankPagination: {},
        bankFilters: { search: '', type: '', difficulty: '', topic: '', skill: '' },

        addingIds: [], // Duplicate preventer
        toasts: [],

        init() {
            if(this.currentSectionId) this.loadSectionQuestions();
        },

        addToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3000);
        },

        loadSectionQuestions(url = null) {
            if(!this.currentSectionId) return;
            this.questionsLoaded = false;
            fetch(url || `/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions`)
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
            if(!confirm('Remove this question?')) return;
            fetch(`/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/remove`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.addToast("Question removed from exam.");
                    this.loadSectionQuestions();
                }
            });
        },

        openBankModal() { this.showBankModal = true; this.loadBankQuestions(); },

        loadBankQuestions(url = null) {
            this.bankLoading = true;
            let baseUrl = url || `/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/available`;
            let fetchUrl = new URL(baseUrl, window.location.origin);

            // Append filters to maintain them during pagination
            Object.keys(this.bankFilters).forEach(key => {
                if (this.bankFilters[key]) fetchUrl.searchParams.append(key, this.bankFilters[key]);
            });

            fetch(fetchUrl.toString())
                .then(r => r.json())
                .then(data => {
                    this.bankQuestions = data.data;
                    this.bankPagination = data;
                    this.bankLoading = false;
                }).catch(() => this.bankLoading = false);
        },

        changeBankPage(url) { if(url) this.loadBankQuestions(url); },

        resetFilters() {
            this.bankFilters = { search: '', type: '', difficulty: '', topic: '', skill: '' };
            this.loadBankQuestions();
        },

        addQuestion(id) {
            if(this.addingIds.includes(id)) return;
            this.addingIds.push(id);

            fetch(`/admin/exams/${this.examId}/sections/${this.currentSectionId}/questions/add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question_id: id })
            }).then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    this.addToast("Question added successfully!");
                    this.loadSectionQuestions();

                    // FIX: Gap filler logic - Refresh list to get next question from bank
                    this.loadBankQuestions(this.bankPagination.path + '?page=' + this.bankPagination.current_page);
                } else {
                    this.addToast(data.message, 'error');
                }
            }).finally(() => {
                this.addingIds = this.addingIds.filter(i => i !== id);
            });
        }
    }
}
</script>

<style>
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .prose-sm p { margin: 0; }
</style>
@endsection
