@extends('layouts.admin')

@section('content')
    {{-- Toast Notification --}}
    <div x-data="{
        show: false,
        message: '',
        init() {
            @if (session('success')) this.showToast('{{ session('success') }}'); @endif
        },
        showToast(msg) {
            this.message = msg;
            this.show = true;
            setTimeout(() => { this.show = false }, 3000);
        }
    }" x-init="init()" class="fixed top-5 right-5 z-[100]">
        <div x-show="show" x-transition
            class="flex items-center gap-3 px-6 py-3 bg-white border-l-4 border-[var(--brand-green)] shadow-2xl rounded-xl">
            <div class="p-1 bg-[var(--brand-green)]/10 text-[var(--brand-green)] rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="text-sm font-black text-gray-800 uppercase tracking-tight" x-text="message"></span>
        </div>
    </div>

    <div class="max-w-6xl py-6 mx-auto">

        {{-- 1. Steps Navigation --}}
        @include('admin.exams.partials._steps', ['activeStep' => 'sections'])

        <div class="mt-6 space-y-6">

            {{-- 2. Header & Add Button --}}
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Manage Sections</h2>
                    <p class="text-sm text-gray-500">Configure sections for <strong>{{ $exam->title }}</strong></p>
                </div>
                <button type="button" onclick="openAddModal()" style="background-color: var(--brand-blue);"
                    class="flex items-center gap-2 px-7 py-3 font-black text-white transition-all rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:scale-95 uppercase text-xs tracking-[0.1em]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Section
                </button>
            </div>

            {{-- 3. Data Table --}}
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Order
                                </th>
                                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Display
                                    Name</th>
                                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Section
                                </th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">
                                    Total Questions</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">
                                    Total Duration</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">
                                    Total Marks</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($exam->examSections as $section)
                                <tr class="transition-colors hover:bg-gray-50/80 group">
                                    <td class="px-6 py-4 text-gray-400 font-bold">#{{ $section->section_order }}</td>
                                    <td class="px-6 py-4"><span
                                            class="font-bold text-gray-800 group-hover:text-[var(--brand-blue)] transition-colors">{{ $section->name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2.5 py-1 text-[10px] font-black uppercase text-[var(--brand-blue)] bg-[var(--brand-blue)]/10 rounded-lg border border-[var(--brand-blue)]/20">
                                            {{ $section->section->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono text-sm font-bold text-gray-700">
                                        {{ $section->questions_count }} Q</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-bold text-gray-700">{{ floor($section->total_duration / 60) }}
                                                Mins</span>
                                            @if ($exam->settings['auto_duration'] ?? true)
                                                <span
                                                    class="text-[9px] font-black text-[var(--brand-green)] uppercase">Auto</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-bold text-gray-700 text-sm">{{ $section->total_marks }}</span>
                                            @if ($exam->settings['auto_grading'] ?? true)
                                                <span
                                                    class="text-[9px] font-black text-[var(--brand-green)] uppercase">Auto</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button onclick="editSection({{ $section->id }})"
                                                class="p-2 text-gray-400 hover:text-[var(--brand-blue)] hover:bg-[var(--brand-blue)]/10 rounded-xl transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form
                                                action="{{ route('admin.exams.sections.destroy', [$exam->id, $section->id]) }}"
                                                method="POST" onsubmit="return confirm('Delete this section?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-gray-400 hover:text-[var(--brand-pink)] hover:bg-[var(--brand-pink)]/10 rounded-xl transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-20 text-center text-[var(--brand-pink)] font-bold">No
                                        sections added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Next Button --}}
            <div class="flex justify-end mt-6 gap-3">
                <a href="{{ route('admin.exams.index') }}"
                    class="px-8 py-3.5 font-bold text-gray-500 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Save
                    & Exit</a>
                @if ($exam->examSections->count() > 0)
                    <a href="{{ route('admin.exams.questions.index', $exam->id) }}"
                        style="background-color: var(--brand-blue);"
                        class="flex items-center gap-3 px-10 py-3.5 font-black text-white rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                        <span>Next: Add Questions</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div id="sectionModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/40 backdrop-blur-sm" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-3xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form id="sectionForm" method="POST">
                    @csrf
                    <div id="methodField"></div>
                    <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight" id="modalTitle">Add Section
                        </h3>
                        <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {{-- Name --}}
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Display Name
                                    <span class="text-[var(--brand-pink)]">*</span></label>
                                <input type="text" name="name" required
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all"
                                    placeholder="e.g. Reasoning Ability">
                            </div>

                            {{-- Section Type - Custom Dropdown UI with Event Listener --}}
                            <div class="space-y-2"
                                 x-data="{ open: false, selected: 'Select Type', value: '' }"
                                 @set-section-type.window="selected = $event.detail.text; value = $event.detail.value">

                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Section Type
                                    <span class="text-[var(--brand-pink)]">*</span></label>
                                <div class="relative">
                                    <input type="hidden" name="section_id" :value="value">
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 flex justify-between items-center focus:ring-4 focus:ring-[var(--brand-blue)]/10">
                                        <span x-text="selected" :class="value ? 'text-gray-900' : 'text-gray-400'"></span>
                                        <svg class="w-4 h-4 text-gray-400" :class="open ? 'rotate-180' : ''"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 9l-7 7-7-7" stroke-width="2.5" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition
                                        class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl max-h-48 overflow-y-auto no-scrollbar">
                                        @foreach ($availableSections as $s)
                                            <div @click="selected = '{{ $s->name }}'; value = '{{ $s->id }}'; open = false"
                                                class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white transition-colors">
                                                {{ $s->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Order --}}
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Order <span
                                        class="text-[var(--brand-pink)]">*</span></label>
                                <input type="number" name="section_order" required
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                    value="{{ $exam->examSections->count() + 1 }}">
                            </div>

                            {{-- Correct Marks --}}
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Correct
                                    Marks</label>
                                <input type="number" name="correct_marks" step="0.01" min="0" required
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                    placeholder="2.00">
                            </div>

                            {{-- Negative Marks Custom Dropdown --}}
                            <div class="space-y-2" x-data="{ open: false, type: 'fixed' }">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Negative
                                    Marks</label>
                                <div class="flex gap-2">
                                    <div class="relative w-1/3">
                                        <input type="hidden" name="negative_marking_type" :value="type">
                                        <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full h-full px-2 py-3 text-xs font-bold border border-gray-200 rounded-xl bg-gray-50/50 flex justify-between items-center">
                                            <span x-text="type === 'fixed' ? 'Fixed' : '%'"></span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path d="M19 9l-7 7-7-7" stroke-width="3" />
                                            </svg>
                                        </button>
                                        <div x-show="open"
                                            class="absolute z-50 w-full mt-1 bg-white border border-gray-100 rounded-lg shadow-lg">
                                            <div @click="type='fixed'; open=false"
                                                class="px-3 py-2 text-xs cursor-pointer hover:bg-gray-100">Fixed</div>
                                            <div @click="type='percentage'; open=false"
                                                class="px-3 py-2 text-xs cursor-pointer hover:bg-gray-100">%</div>
                                        </div>
                                    </div>
                                    <input type="number" name="negative_marks" step="0.01" min="0"
                                        class="w-2/3 px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                        value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-8 py-6 bg-gray-50/50 rounded-b-3xl">
                        <button type="button" onclick="closeModal()"
                            class="px-6 py-2.5 font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                        <button type="submit" style="background-color: var(--brand-blue);"
                            class="px-10 py-3 font-black text-white rounded-xl shadow-lg hover:shadow-xl active:scale-95 transition-all">SAVE
                            SECTION</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('sectionModal');
        const form = document.getElementById('sectionForm');
        const examId = "{{ $exam->id }}";

        // Pass PHP data to JS for lookup
        const allSections = @json($availableSections);

        function toggleModal(show) {
            modal.classList.toggle('hidden', !show);
            document.body.style.overflow = show ? 'hidden' : 'auto';
        }

        function closeModal() {
            toggleModal(false);
        }

        function openAddModal() {
            form.reset();
            document.getElementById('modalTitle').innerText = "Add New Section";
            document.getElementById('methodField').innerHTML = "";
            form.action = `/admin/exams/${examId}/sections`;

            // RESET ALPINE DROPDOWN
            window.dispatchEvent(new CustomEvent('set-section-type', {
                detail: { text: 'Select Type', value: '' }
            }));

            toggleModal(true);
        }

        function editSection(id) {
            fetch(`/admin/exams/${examId}/sections/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalTitle').innerText = "Edit Section";
                    document.getElementById('methodField').innerHTML = '@method('PUT')';
                    form.action = `/admin/exams/${examId}/sections/${id}`;

                    form.querySelector('[name="name"]').value = data.name;
                    form.querySelector('[name="section_order"]').value = data.section_order;
                    form.querySelector('[name="correct_marks"]').value = data.correct_marks;
                    form.querySelector('[name="negative_marks"]').value = data.negative_marks;

                    // FIND SECTION NAME FROM ID AND UPDATE ALPINE
                    const matchedSection = allSections.find(s => s.id == data.section_id);
                    const sectionName = matchedSection ? matchedSection.name : 'Unknown Type';

                    // Dispatch event to update AlpineJS UI
                    window.dispatchEvent(new CustomEvent('set-section-type', {
                        detail: { text: sectionName, value: data.section_id }
                    }));

                    toggleModal(true);
                })
                .catch(err => {
                    console.error(err);
                    alert('Error loading section details.');
                });
        }
    </script>

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endsection
