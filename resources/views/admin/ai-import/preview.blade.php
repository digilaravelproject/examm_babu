@extends('layouts.admin')

@section('content')
    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- Header Section --}}
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 sticky top-0 bg-slate-50/90 backdrop-blur-md z-50 py-4 -mx-4 px-4">
                <div>
                    <nav class="flex mb-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.ai-import.index') }}"
                                    class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors flex items-center">
                                    <i class="fas fa-magic mr-2 text-[10px]"></i> AI Import
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center text-slate-300">
                                    <i class="fas fa-chevron-right text-[8px] mx-1"></i>
                                    <span class="text-sm font-bold text-slate-900 ml-1">Review Questions</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                        Verify Extracted Content
                    </h1>
                    <p class="text-slate-500 mt-1 flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 bg-indigo-100 text-indigo-600 rounded-full text-[10px] font-black">{{ count($questions) }}</span>
                        Questions ready for review
                    </p>
                </div>

                {{-- Stepper Progress --}}
                <div
                    class="hidden lg:flex items-center gap-6 bg-white px-8 py-4 rounded-3xl border border-slate-200 shadow-sm overflow-x-auto whitespace-nowrap">
                    <div class="flex items-center gap-3 shrink-0">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-emerald-100">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Config</span>
                    </div>
                    <div class="h-px w-8 bg-slate-200"></div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-emerald-100">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Extract</span>
                    </div>
                    <div class="h-px w-8 bg-slate-200"></div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-indigo-100 scale-110">
                            3</div>
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Review</span>
                    </div>
                    <div class="h-px w-8 bg-slate-200"></div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div
                            class="w-8 h-8 rounded-full bg-white border-2 border-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold">
                            4</div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Approve</span>
                    </div>
                </div>

                @if (count($questions) > 0)
                    <div class="flex items-center gap-3">
                        <button onclick="cancelSession('{{ $batchId }}')"
                            class="px-6 py-3 text-slate-500 font-bold hover:text-rose-600 transition-colors">Cancel</button>
                        <button id="approveBtn"
                            class="inline-flex items-center px-10 py-4 bg-indigo-600 border border-transparent rounded-2xl font-black text-white hover:bg-indigo-700 hover:scale-[1.02] active:scale-95 transition-all shadow-xl shadow-indigo-200 uppercase tracking-widest text-sm">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> Save to Database
                        </button>
                    </div>
                @endif
            </div>

            <div id="status-message"
                class="hidden p-6 mb-8 rounded-2xl border-2 transition-all duration-500 animate-in slide-in-from-top-4">
            </div>

            @if (count($questions) > 0)
                {{-- Search & Control Bar --}}
                <div
                    class="mb-6 flex flex-col md:flex-row items-center gap-4 bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="questionSearch" placeholder="Search questions by keyword..."
                            class="block w-full pl-11 pr-4 py-3 bg-slate-50 border-transparent rounded-2xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all placeholder:text-slate-400">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">View:</span>
                        <button class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100">
                            <i class="fas fa-list-ul"></i>
                        </button>
                    </div>
                </div>
            @endif

            @if (count($questions) == 0)
                <div class="bg-white rounded-3xl p-20 text-center border-2 border-dashed border-slate-200 shadow-sm space-y-6">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-300">
                        <i class="fas fa-ghost text-5xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900 tracking-tight">No Questions Found</h3>
                        <p class="text-slate-500 max-w-sm mx-auto mt-2 font-medium">AI couldn't find any questions matching
                            our quality standards. This usually happens if the PDF scan is poor or text is unrecognizable.
                        </p>
                    </div>
                    <a href="{{ route('admin.ai-import.index') }}"
                        class="inline-block px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">Try
                        a Different Scan</a>
                </div>
            @else
                {{-- Compact List View --}}
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-0">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th
                                        class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-16 border-b border-slate-200">
                                        #</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-200">
                                        Question Preview</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-24 border-b border-slate-200 text-center">
                                        Type</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-20 border-b border-slate-200 text-center">
                                        Page</th>
                                    <th
                                        class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-40 border-b border-slate-200 text-right">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="questionTableBody" class="divide-y divide-slate-100">
                                @foreach ($questions as $index => $q)
                                    <tr class="group hover:bg-indigo-50/30 transition-all cursor-pointer"
                                        onclick="toggleDetails({{ $index }}, event)">
                                        <td class="px-6 py-5 align-top">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 text-slate-500 font-bold text-[11px] group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div
                                                class="question-preview text-sm font-bold text-slate-700 leading-snug line-clamp-2 group-hover:text-slate-900 transition-colors">
                                                {!! strip_tags($q['question']) !!}
                                            </div>
                                            <div class="flex items-center gap-3 mt-2">
                                                @if (isset($q['image_box']))
                                                    <span
                                                        class="text-[9px] font-black uppercase text-indigo-400 bg-indigo-50/50 px-1.5 py-0.5 rounded border border-indigo-100 flex items-center">
                                                        <i class="fas fa-image mr-1"></i> Diagram Detected
                                                    </span>
                                                @endif
                                                @if (count($q['options'] ?? []) > 0)
                                                    <span class="text-[9px] font-black uppercase text-slate-400 flex items-center">
                                                        <i class="fas fa-th-list mr-1"></i> {{ count($q['options']) }}
                                                        Options
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-top text-center">
                                            <span
                                                class="text-[9px] font-black uppercase bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-lg border border-indigo-100">
                                                {{ $q['type'] ?? 'MSA' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 align-top text-center">
                                            <span class="text-xs font-bold text-slate-400 tabular-nums">
                                                P.{{ $q['source_page'] ?? '?' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right align-top space-x-1">
                                            <button type="button" onclick="editQuestion({{ $index }}, event)"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-white hover:text-indigo-600 hover:shadow-md transition-all">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button type="button"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-300 hover:text-indigo-600 transition-all">
                                                <i id="icon-{{ $index }}"
                                                    class="fas fa-chevron-down text-[10px] transition-transform duration-300"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    {{-- Detailed View --}}
                                    <tr id="details-{{ $index }}" class="hidden bg-slate-50/40">
                                        <td colspan="5"
                                            class="px-12 py-10 border-t border-slate-100 bg-gradient-to-b from-slate-50/50 to-white">
                                            <div class="max-w-4xl space-y-10 animate-in fade-in slide-in-from-top-2 duration-300">
                                                {{-- Full Question --}}
                                                <div class="space-y-4">
                                                    <label
                                                        class="text-[10px] font-black uppercase text-indigo-400 tracking-widest flex items-center gap-2">
                                                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full"></span> Full
                                                        Question Text
                                                    </label>
                                                    <div
                                                        class="question-full prose prose-sm max-w-none text-slate-800 font-bold leading-relaxed text-lg">
                                                        {!! $q['question'] !!}
                                                    </div>
                                                </div>

                                                {{-- Options Grid --}}
                                                @if (isset($q['options']) && is_array($q['options']) && count($q['options']) > 0)
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                        @foreach ($q['options'] as $optIdx => $opt)
                                                            @php
                                                                $isCorrect = false;
                                                                if (($q['type'] ?? 'MSA') === 'MMA') {
                                                                    $isCorrect = in_array(
                                                                        $optIdx,
                                                                        $q['correct_option_indices'] ?? [],
                                                                    );
                                                                } else {
                                                                    $isCorrect =
                                                                        isset($q['correct_option_index']) &&
                                                                        $q['correct_option_index'] == $optIdx;
                                                                }
                                                                $letter = chr(65 + $optIdx);
                                                            @endphp
                                                            <div
                                                                class="p-5 rounded-2xl border-2 transition-all {{ $isCorrect ? 'bg-emerald-50/50 border-emerald-200 shadow-sm' : 'bg-white border-slate-100' }}">
                                                                <div class="flex items-start gap-4">
                                                                    <span
                                                                        class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-black {{ $isCorrect ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-100' : 'bg-slate-100 text-slate-400' }}">
                                                                        {{ $letter }}
                                                                    </span>
                                                                    <div
                                                                        class="text-sm font-bold leading-snug {{ $isCorrect ? 'text-emerald-900' : 'text-slate-600' }}">
                                                                        {!! $opt !!}
                                                                    </div>
                                                                    @if ($isCorrect)
                                                                        <i
                                                                            class="fas fa-check-circle text-emerald-500 ml-auto self-center text-lg"></i>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif(isset($q['correct_answer_text']))
                                                    <div
                                                        class="p-6 bg-indigo-50/50 border-2 border-indigo-100 rounded-3xl flex items-center gap-5">
                                                        <div
                                                            class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-xl shadow-indigo-100">
                                                            <i class="fas fa-key text-lg"></i>
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-[10px] uppercase font-black text-indigo-400 tracking-widest mb-1">Correct
                                                                Answer</label>
                                                            <span
                                                                class="text-lg font-black text-indigo-900">{{ $q['correct_answer_text'] }}</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Meta Details --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-slate-100">
                                                    @if (!empty($q['solution']))
                                                        <div class="space-y-2">
                                                            <label
                                                                class="text-[10px] font-black uppercase text-slate-400 tracking-widest flex items-center gap-2">
                                                                <i class="fas fa-lightbulb text-amber-400"></i> AI Solution
                                                            </label>
                                                            <div
                                                                class="p-4 bg-slate-100/50 rounded-2xl text-[11px] text-slate-600 leading-relaxed font-medium italic border border-slate-200/50">
                                                                {!! $q['solution'] !!}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if (!empty($q['hint']))
                                                        <div class="space-y-2">
                                                            <label
                                                                class="text-[10px] font-black uppercase text-slate-400 tracking-widest flex items-center gap-2">
                                                                <i class="fas fa-info-circle text-blue-400"></i> AI Hint
                                                            </label>
                                                            <div
                                                                class="p-4 bg-slate-100/50 rounded-2xl text-[11px] text-slate-600 leading-relaxed font-medium italic border border-slate-200/50">
                                                                {!! $q['hint'] !!}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-md transition-opacity" onclick="closeEditModal()">
            </div>
            <div
                class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl overflow-hidden animate-in zoom-in-95 duration-200 border border-white/20">
                <div class="px-10 py-8 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Edit Question</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Refine extracted
                            content for accuracy</p>
                    </div>
                    <button onclick="closeEditModal()"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm text-slate-400 hover:text-slate-600 transition-colors border border-slate-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-10 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <form id="editForm" class="space-y-8">
                        <input type="hidden" id="editIndex">
                        <div class="space-y-3">
                            <label class="block text-[11px] font-black uppercase text-slate-500 tracking-widest">Question
                                Body <span class="text-indigo-400 font-bold ml-2">(HTML Supported)</span></label>
                            <textarea id="editQuestionText" rows="5"
                                class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-3xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-bold leading-relaxed"></textarea>
                        </div>

                        <div id="optionsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Options injected via JS --}}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                            <div class="space-y-3">
                                <label
                                    class="block text-[11px] font-black uppercase text-slate-500 tracking-widest">Explanatory
                                    Solution</label>
                                <textarea id="editSolution" rows="4"
                                    class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-3xl text-[12px] focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all italic leading-relaxed"></textarea>
                            </div>
                            <div class="space-y-3">
                                <label class="block text-[11px] font-black uppercase text-slate-500 tracking-widest">Short
                                    Hint</label>
                                <textarea id="editHint" rows="4"
                                    class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-3xl text-[12px] focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all italic leading-relaxed"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-100 flex justify-end gap-4">
                    <button onclick="closeEditModal()"
                        class="px-8 py-3 text-sm font-bold text-slate-500 hover:text-slate-800 transition-all">Discard
                        Changes</button>
                    <button id="saveEditBtn" onclick="saveQuestionEdit()"
                        class="px-10 py-3.5 bg-indigo-600 text-white text-sm font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest flex items-center">
                        <i class="fas fa-save mr-2"></i> Update Entry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        let questionsData = @json($questions);

        function toggleDetails(index, event) {
            // Prevent trigger if clicking on specific buttons
            if (event.target.closest('button') && !event.target.closest('button').classList.contains('w-9')) return;

            const row = document.getElementById(`details-${index}`);
            const icon = document.getElementById(`icon-${index}`);
            const isHidden = row.classList.contains('hidden');

            // Close other open details
            document.querySelectorAll('tr[id^="details-"]:not(.hidden)').forEach(el => {
                if (el.id !== `details-${index}`) {
                    el.classList.add('hidden');
                    const otherIdx = el.id.split('-')[1];
                    document.getElementById(`icon-${otherIdx}`).classList.remove('rotate-180');
                }
            });

            if (isHidden) {
                row.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                row.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Search functionality
        document.getElementById('questionSearch').addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#questionTableBody > tr:not([id^="details-"])');

            rows.forEach((row, idx) => {
                const text = row.querySelector('.question-preview').textContent.toLowerCase();
                const detailRow = document.getElementById(`details-${idx}`);

                if (text.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    detailRow.classList.add('hidden');
                    document.getElementById(`icon-${idx}`).classList.remove('rotate-180');
                }
            });
        });

        // Edit functionality
        function editQuestion(index, event) {
            if (event) event.stopPropagation();

            const q = questionsData[index];
            document.getElementById('editIndex').value = index;
            document.getElementById('editQuestionText').value = q.question;
            document.getElementById('editSolution').value = q.solution || '';
            document.getElementById('editHint').value = q.hint || '';

            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';

            if (q.options && Array.isArray(q.options)) {
                q.options.forEach((opt, optIdx) => {
                    const isCorrect = (q.type === 'MMA') ?
                        (q.correct_option_indices || []).includes(optIdx) :
                        (q.correct_option_index == optIdx);

                    const div = document.createElement('div');
                    div.className =
                        `p-5 rounded-3xl border-2 transition-all ${isCorrect ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-100'}`;
                    div.innerHTML = `
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-[10px] font-black text-slate-400 tracking-tighter">OPTION ${String.fromCharCode(65 + optIdx)}</span>
                            ${isCorrect ? '<span class="ml-auto text-[9px] font-black text-emerald-600 uppercase bg-emerald-100 px-1.5 py-0.5 rounded">Correct</span>' : ''}
                        </div>
                        <textarea class="option-input w-full bg-white border border-slate-200 rounded-2xl p-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all" data-idx="${optIdx}" rows="2">${opt}</textarea>
                    `;
                    container.appendChild(div);
                });
            }

            document.getElementById('editModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        async function saveQuestionEdit() {
            const btn = document.getElementById('saveEditBtn');
            const index = document.getElementById('editIndex').value;
            const newText = document.getElementById('editQuestionText').value;
            const newSolution = document.getElementById('editSolution').value;
            const newHint = document.getElementById('editHint').value;

            const optInputs = document.querySelectorAll('.option-input');
            const newOptions = [];
            optInputs.forEach(input => {
                newOptions[input.dataset.idx] = input.value;
            });

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Syncing...';

            // Update local state
            questionsData[index].question = newText;
            questionsData[index].options = newOptions;
            questionsData[index].solution = newSolution;
            questionsData[index].hint = newHint;

            try {
                const res = await fetch("{{ route('admin.ai-import.update-json', $batchId) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        questions: questionsData
                    })
                });
                const data = await res.json();
                if (data.success) {
                    // Update UI visually
                    const mainRow = document.querySelector(
                        `#questionTableBody > tr:nth-child(${parseInt(index) * 2 + 1})`);
                    mainRow.querySelector('.question-preview').innerHTML = newText.replace(/<[^>]*>?/gm, '');

                    const detailRow = document.getElementById(`details-${index}`);
                    detailRow.querySelector('.question-full').innerHTML = newText;

                    // Update options in detail view
                    const detailOptions = detailRow.querySelectorAll('.text-sm.font-bold');
                    newOptions.forEach((opt, i) => {
                        if (detailOptions[i]) detailOptions[i].innerHTML = opt;
                    });

                    closeEditModal();
                    // Simple toast replacement
                    const statusMsg = document.getElementById('status-message');
                    statusMsg.className =
                        "p-4 mb-8 rounded-2xl border-2 bg-emerald-50 border-emerald-100 text-emerald-800 font-bold flex items-center fixed bottom-10 right-10 z-[100] shadow-2xl animate-in fade-in slide-in-from-bottom-5";
                    statusMsg.innerHTML =
                        '<i class="fas fa-check-circle text-emerald-500 mr-3"></i> Item updated successfully';
                    statusMsg.classList.remove('hidden');
                    setTimeout(() => statusMsg.classList.add('hidden'), 3000);
                }
            } catch (err) {
                alert('Failed to sync update to server.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-2"></i> Update Entry';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const approveBtn = document.getElementById('approveBtn');
            const statusMsg = document.getElementById('status-message');

            if (approveBtn) {
                approveBtn.addEventListener('click', async function () {
                    if (!confirm(
                        "Ready to integrate these into your database? This action cannot be undone."
                    )) return;

                    approveBtn.disabled = true;
                    const originalContent = approveBtn.innerHTML;
                    approveBtn.innerHTML =
                        '<i class="fas fa-spinner fa-spin mr-2"></i> Finalizing Database...';
                    approveBtn.classList.replace('bg-indigo-600', 'bg-slate-400');

                    // Update Stepper visually to Step 4
                    const step3Ind = document.querySelector('.lg\\:flex .bg-indigo-600');
                    if (step3Ind) {
                        step3Ind.classList.replace('bg-indigo-600', 'bg-emerald-500');
                        step3Ind.innerHTML = '<i class="fas fa-check"></i>';
                        step3Ind.parentElement.querySelector('span').classList.replace(
                            'text-indigo-600', 'text-emerald-600');

                        const step4Ind = document.querySelectorAll('.lg\\:flex .bg-white')[0]; // Step 4
                        if (step4Ind) {
                            step4Ind.classList.replace('bg-white', 'bg-indigo-600');
                            step4Ind.classList.add('text-white', 'scale-110');
                            step4Ind.classList.remove('border-2', 'border-slate-200', 'text-slate-400');
                            step4Ind.parentElement.querySelector('span').classList.replace(
                                'text-slate-400', 'text-indigo-600');
                        }
                    }

                    try {
                        const res = await fetch("{{ route('admin.ai-import.approve', $batchId) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await res.json();

                        if (data.success) {
                            statusMsg.className =
                                "p-8 mb-8 rounded-3xl border-2 bg-emerald-50 border-emerald-100 text-emerald-800 font-black flex items-center text-xl shadow-2xl";
                            statusMsg.innerHTML =
                                '<i class="fas fa-check-circle text-4xl mr-6 text-emerald-500"></i>' +
                                data.message;
                            statusMsg.classList.remove('hidden');

                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                            setTimeout(() => window.location.href = data.redirect, 2000);
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (err) {
                        approveBtn.disabled = false;
                        approveBtn.innerHTML = originalContent;
                        approveBtn.classList.replace('bg-slate-400', 'bg-indigo-600');

                        statusMsg.className =
                            "p-8 mb-8 rounded-3xl border-2 bg-rose-50 border-rose-100 text-rose-800 font-black flex items-center shadow-2xl";
                        statusMsg.innerHTML =
                            '<i class="fas fa-exclamation-triangle text-3xl mr-6 text-rose-500"></i>' +
                            err.message;
                        statusMsg.classList.remove('hidden');
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            }
        });

        async function cancelSession(batchId) {
            if (!confirm("Are you sure you want to cancel and delete this import session?")) return;

            try {
                const res = await fetch(`{{ route('admin.ai-import.cancel') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ batch_id: batchId })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = "{{ route('admin.ai-import.index') }}";
                } else {
                    alert(data.message || "Failed to cancel session.");
                }
            } catch (err) {
                console.error("Cancel failed:", err);
                alert("An error occurred while canceling.");
            }
        }
    </script>
@endsection
