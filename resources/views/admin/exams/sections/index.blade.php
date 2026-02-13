@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('content')
    {{-- Toast Notification --}}
    <div x-data="{
            show: false,
            message: '',
            init() {
                @if (session('success')) this.showToast('{{ session('success') }}'); @endif
                @if (session('error')) this.showToast('{{ session('error') }}'); @endif
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
            <span class="text-sm font-black tracking-tight text-gray-800 uppercase" x-text="message"></span>
        </div>
    </div>

    @php
        // --- Dynamic Route Logic ---
        $isAdmin = request()->routeIs('admin.*');
        $routePrefix = $isAdmin ? 'admin.' : 'panel.';
        $urlPrefix = $isAdmin ? 'admin' : 'instructor';

        $routeParams = [];
        if (!$isAdmin) {
            $routeParams = ['role' => request()->route('role') ?? 'instructor'];
        }

        // Generate URLs
        $urlQuestions = route($routePrefix . 'exams.questions.index', array_merge($routeParams, ['exam' => $exam->id]));
        $urlIndex = route($routePrefix . 'exams.index', $routeParams);
    @endphp

    <div class="max-w-6xl py-6 mx-auto">

        {{-- 1. Steps Navigation --}}
        @include('admin.exams.partials._steps', ['activeStep' => 'sections', 'routePrefix' => $routePrefix, 'routeParams' => $routeParams])

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
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Order
                                </th>
                                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Display
                                    Name</th>
                                <th class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase">Micro
                                    Category
                                </th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">
                                    Questions</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">
                                    Duration</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-center">
                                    Marks</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black tracking-widest text-gray-400 uppercase text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($exam->examSections as $section)
                                <tr class="transition-colors hover:bg-gray-50/80 group">
                                    <td class="px-6 py-4 font-bold text-gray-400">#{{ $section->section_order }}</td>
                                    <td class="px-6 py-4"><span
                                            class="font-bold text-gray-800 group-hover:text-[var(--brand-blue)] transition-colors">{{ $section->name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2.5 py-1 text-[10px] font-black uppercase text-[var(--brand-blue)] bg-[var(--brand-blue)]/10 rounded-lg border border-[var(--brand-blue)]/20">
                                            {{ $section->microCategory->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-sm font-bold text-center text-gray-700">
                                        {{ $section->questions_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-bold text-gray-700">{{ floor($section->total_duration / 60) }}
                                                m</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-bold text-gray-700">{{ $section->total_marks }}</span>
                                            @if ($exam->settings['auto_grading'] ?? true)
                                                <span class="text-[9px] font-black text-[var(--brand-green)] uppercase">Auto</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button onclick="editSection({{ $section->id }})"
                                                class="p-2 text-gray-400 hover:text-[var(--brand-blue)] hover:bg-[var(--brand-blue)]/10 rounded-xl transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            {{-- Dynamic Delete Route --}}
                                            <form
                                                action="{{ route($routePrefix . 'exams.sections.destroy', array_merge($routeParams, ['exam' => $exam->id, 'section' => $section->id])) }}"
                                                method="POST" onsubmit="return confirm('Delete this section?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-gray-400 hover:text-[var(--brand-pink)] hover:bg-[var(--brand-pink)]/10 rounded-xl transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-20 text-center text-[var(--brand-pink)] font-bold">No sections
                                        added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Next Button --}}
            <div class="flex justify-end gap-3 mt-6">
                {{-- Dynamic Save & Exit --}}
                <a href="{{ $urlIndex }}"
                    class="px-8 py-3.5 font-bold text-gray-500 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Save
                    & Exit</a>

                @if ($exam->examSections->count() > 0)
                    {{-- Dynamic Next Button --}}
                    <a href="{{ $urlQuestions }}" style="background-color: var(--brand-blue);"
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
                        <h3 class="text-xl font-black tracking-tight text-gray-900 uppercase" id="modalTitle">Add Section
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
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Display Name
                                    <span class="text-[var(--brand-pink)]">*</span></label>
                                <input type="text" name="name" required
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all"
                                    placeholder="e.g. Reasoning Ability">
                            </div>

                            {{-- Section Type - Custom Dropdown UI --}}
                            <div class="space-y-2" x-data="{ open: false, selected: 'Select Type', value: '' }"
                                @set-section-type.window="selected = $event.detail.text; value = $event.detail.value">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Micro Category
                                    <span class="text-[var(--brand-pink)]">*</span></label>
                                <div class="relative">
                                    <input type="hidden" name="micro_category_id" :value="value" id="sectionIdInput">
                                    <button type="button" @click="open = !open" @click.away="open = false"
                                        class="w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 flex justify-between items-center focus:ring-4 focus:ring-[var(--brand-blue)]/10">
                                        <span x-text="selected" :class="value ? 'text-gray-900' : 'text-gray-400'"></span>
                                        <svg class="w-4 h-4 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 9l-7 7-7-7" stroke-width="2.5" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition
                                        class="absolute z-50 w-full mt-2 overflow-y-auto bg-white border border-gray-100 shadow-xl rounded-xl max-h-48 no-scrollbar">
                                        @foreach ($microCategories as $mc)
                                            <div onclick="updateSkillsDropdown({{ $mc->id }})"
                                                @click="selected = '{{ $mc->name }}'; value = '{{ $mc->id }}'; open = false"
                                                class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white transition-colors">
                                                {{ $mc->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- 🔥 SKILLS & TOPICS SELECTION (FILTERED BY JS) 🔥 --}}
                            <div class="hidden p-4 space-y-4 border border-gray-200 md:col-span-2 bg-gray-50 rounded-xl"
                                id="skillsTopicsContainer">
                                {{-- Skill Dropdown --}}
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Select
                                            Subject</label>
                                    </div>
                                    <div x-data="{ open: false, selected: 'Select Subject', selectedId: '' }"
                                        @set-skill-dropdown.window="selected = $event.detail.text; selectedId = $event.detail.id">
                                        <input type="hidden" name="selected_skill" id="selectedSkillId" :value="selectedId">
                                        <button type="button" @click="open = !open" @click.away="open = false"
                                            class="w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-white flex justify-between items-center focus:ring-4 focus:ring-[var(--brand-blue)]/10">
                                            <span x-text="selected"
                                                :class="selectedId ? 'text-gray-900 font-semibold' : 'text-gray-400'"></span>
                                            <svg class="w-4 h-4 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 9l-7 7-7-7" stroke-width="2.5" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition
                                            class="absolute z-50 w-full mt-2 overflow-y-auto bg-white border border-gray-100 shadow-xl rounded-xl max-h-48 no-scrollbar"
                                            style="width: calc(100% - 2rem);">
                                            <div id="skillsDropdown" class="divide-y">
                                                <p class="px-4 py-2.5 text-sm text-gray-400">Loading subjects...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Topics Checkbox Section --}}
                                <div id="topicsContainer" class="hidden space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Select
                                            Topics</label>
                                        {{-- Import Toggle --}}
                                        <label class="relative inline-flex items-center cursor-pointer"
                                            x-data="{ checked: false }"
                                            @set-import-toggle.window="checked = $event.detail.checked">
                                            <input type="checkbox" name="import_questions" value="1" class="sr-only peer"
                                                x-model="checked">
                                            <div
                                                class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--brand-blue)]">
                                            </div>
                                            <span class="ml-2 text-xs font-bold text-gray-700">Import Questions</span>
                                        </label>
                                    </div>

                                    {{-- Topics Checkbox List --}}
                                    <div id="topicsList"
                                        class="grid grid-cols-2 gap-2 p-3 overflow-y-auto bg-white border border-gray-200 rounded-lg max-h-48">
                                        {{-- Populated by JS --}}
                                        <p class="col-span-2 text-xs text-gray-400">Select a subject to see topics...</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Duration --}}
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Duration
                                    (Minutes)
                                    @if(!($exam->settings['auto_duration'] ?? true)) <span
                                    class="text-[var(--brand-pink)]">*</span> @endif
                                </label>
                                <input type="number" name="total_duration" min="0"
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                    placeholder="0 (Auto)" @if($exam->settings['auto_duration'] ?? true) disabled
                                    title="Auto Duration Enabled" @endif>
                            </div>

                            {{-- Correct Marks --}}
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Correct Marks
                                    <span class="text-[var(--brand-pink)]">*</span></label>
                                <input type="number" name="correct_marks" step="0.01" min="0" required
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                    placeholder="1.00">
                            </div>

                            {{-- Negative Marks Custom Dropdown --}}
                            <div class="space-y-2" x-data="{ open: false, type: 'fixed' }"
                                @set-negative-type.window="type = $event.detail.type">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Negative
                                    Marks</label>
                                <div class="flex gap-2">
                                    <div class="relative w-1/3">
                                        <input type="hidden" name="negative_marking_type" :value="type">
                                        <button type="button" @click="open = !open" @click.away="open = false"
                                            class="flex items-center justify-between w-full h-full px-2 py-3 text-xs font-bold border border-gray-200 rounded-xl bg-gray-50/50">
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

                            {{-- Section Cutoff --}}
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Section Cutoff
                                    (%)</label>
                                <input type="number" name="section_cutoff" step="0.01" min="0" max="100"
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                    placeholder="0">
                            </div>

                            {{-- Order --}}
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Order <span
                                        class="text-[var(--brand-pink)]">*</span></label>
                                <input type="number" name="section_order" required
                                    class="w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50/50 focus:ring-[var(--brand-blue)]"
                                    value="{{ $exam->examSections->count() + 1 }}">
                            </div>

                            {{-- Translation Settings (UPDATED) --}}
                            <div class="space-y-3" x-data="{ translationEnabled: false }"
                                @set-translation-toggle.window="translationEnabled = $event.detail.checked">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Allow
                                        Translation</label>

                                    {{-- Toggle Switch --}}
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="allow_translation" value="1" class="sr-only peer"
                                            x-model="translationEnabled">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--brand-blue)]">
                                        </div>
                                        <span class="ml-2 text-xs font-bold text-gray-700"
                                            x-text="translationEnabled ? 'Enabled' : 'Disabled'"></span>
                                    </label>
                                </div>

                                {{-- Translation Language Dropdown (Visible only if enabled) --}}
                                <div x-show="translationEnabled" x-transition
                                    class="space-y-2 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                                    <label class="text-[11px] font-black text-blue-800 uppercase tracking-widest">Select
                                        Language <span class="text-[var(--brand-pink)]">*</span></label>
                                    <select name="translation_language" id="translationLanguageSelect"
                                        class="w-full px-4 py-2.5 text-sm border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-700 font-medium">
                                        <option value="" disabled selected>-- Choose Language --</option>
                                        <option value="hi">Hindi (हिंदी)</option>
                                        <option value="mr">Marathi (मराठी)</option>
                                    </select>
                                    <p class="text-[10px] text-blue-600">Students will see questions in this language.</p>
                                </div>

                                <p x-show="!translationEnabled" class="text-[10px] text-gray-400 leading-tight">If enabled,
                                    students can translate questions in this section.</p>
                            </div>

                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-8 py-6 bg-gray-50/50 rounded-b-3xl">
                        <button type="button" onclick="closeModal()"
                            class="px-6 py-2.5 font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                        <button type="submit" style="background-color: var(--brand-blue);"
                            class="px-10 py-3 font-black text-white transition-all shadow-lg rounded-xl hover:shadow-xl active:scale-95">SAVE
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
        const urlPrefix = "{{ $urlPrefix }}";

        // Renamed variable to reflect new data source
        const allMicroCategories = @json($microCategories);
        let allSkillsTopics = {};
        let previouslySelectedTopics = [];
        let currentSelectedMicroCategoryId = null;

        function initializeSkillsTopicsData() {
            allMicroCategories.forEach(mc => {
                if (mc.skills && mc.skills.length > 0) {
                    mc.skills.forEach(skill => {
                        if (skill.id && !allSkillsTopics[skill.id]) {
                            allSkillsTopics[skill.id] = {
                                name: skill.name,
                                microCategoryId: mc.id, // Changed from sectionId
                                topics: skill.topics || []
                            };
                        }
                    });
                }
            });
        }

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
            form.action = `/${urlPrefix}/exams/${examId}/sections`;

            // Reset UI
            window.dispatchEvent(new CustomEvent('set-section-type', { detail: { text: 'Select Micro Category', value: '' } }));
            window.dispatchEvent(new CustomEvent('set-negative-type', { detail: { type: 'fixed' } }));
            window.dispatchEvent(new CustomEvent('set-import-toggle', { detail: { checked: false } }));
            window.dispatchEvent(new CustomEvent('set-skill-dropdown', { detail: { text: 'Select Subject', id: '' } }));

            // Reset Translation
            window.dispatchEvent(new CustomEvent('set-translation-toggle', { detail: { checked: false } }));
            document.getElementById('translationLanguageSelect').value = "";

            // Reset Skills & Topics UI
            previouslySelectedTopics = [];
            document.getElementById('skillsTopicsContainer').classList.add('hidden');
            document.getElementById('topicsContainer').classList.add('hidden');
            document.getElementById('skillsDropdown').innerHTML = '<p class="px-4 py-2.5 text-sm text-gray-400">Loading subjects...</p>';
            document.getElementById('topicsList').innerHTML = '<p class="col-span-2 text-xs text-gray-400">Select a subject to see topics...</p>';
            currentSelectedMicroCategoryId = null;

            toggleModal(true);
        }

        // Renamed function to match logic
        function updateSkillsDropdown(microCategoryId) {
            const container = document.getElementById('skillsTopicsContainer');
            const dropdown = document.getElementById('skillsDropdown');
            currentSelectedMicroCategoryId = microCategoryId;

            const selectedMCData = allMicroCategories.find(s => s.id == microCategoryId);

            if (!selectedMCData || !selectedMCData.skills || selectedMCData.skills.length === 0) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');
            dropdown.innerHTML = '';

            selectedMCData.skills.forEach(skill => {
                const html = `
                        <div onclick="selectSkill(${skill.id}, '${skill.name.replace(/'/g, "\\'")}', this)"
                             class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white transition-colors">
                            ${skill.name}
                        </div>
                    `;
                dropdown.insertAdjacentHTML('beforeend', html);
            });
        }

        function selectSkill(skillId, skillName, element) {
            const topicsContainer = document.getElementById('topicsContainer');
            const topicsList = document.getElementById('topicsList');

            const event = new CustomEvent('set-skill-dropdown', {
                detail: { text: skillName, id: skillId }
            });
            window.dispatchEvent(event);

            const skillData = allSkillsTopics[skillId];
            if (!skillData || !skillData.topics || skillData.topics.length === 0) {
                topicsContainer.classList.add('hidden');
                topicsList.innerHTML = '<p class="col-span-2 text-xs text-gray-400">No topics available for this subject.</p>';
                return;
            }

            topicsContainer.classList.remove('hidden');
            topicsList.innerHTML = '';

            skillData.topics.forEach(topic => {
                const isChecked = previouslySelectedTopics.includes(topic.id) ? 'checked' : '';

                const html = `
                        <label class="flex items-center p-2 space-x-2 transition-colors border border-gray-100 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="selected_topics[]" value="${topic.id}" ${isChecked} class="text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-xs font-medium text-gray-700">${topic.name}</span>
                        </label>
                    `;
                topicsList.insertAdjacentHTML('beforeend', html);
            });
        }

        function editSection(id) {
            fetch(`/${urlPrefix}/exams/${examId}/sections/${id}/edit`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    document.getElementById('modalTitle').innerText = "Edit Section";
                    document.getElementById('methodField').innerHTML = '@method('PUT')';
                    form.action = `/${urlPrefix}/exams/${examId}/sections/${id}`;

                    form.querySelector('[name="name"]').value = data.name || '';
                    form.querySelector('[name="section_order"]').value = data.section_order || '';
                    form.querySelector('[name="correct_marks"]').value = data.correct_marks || '';
                    form.querySelector('[name="negative_marks"]').value = data.negative_marks || '';
                    form.querySelector('[name="section_cutoff"]').value = data.section_cutoff || '';
                    form.querySelector('[name="total_duration"]').value = data.duration_minutes || '';

                    // Match MicroCategory instead of Section
                    const matchedMC = allMicroCategories.find(s => s.id == data.micro_category_id);
                    const mcName = matchedMC ? matchedMC.name : 'Unknown Category';

                    window.dispatchEvent(new CustomEvent('set-section-type', {
                        detail: { text: mcName, value: data.micro_category_id }
                    }));

                    window.dispatchEvent(new CustomEvent('set-negative-type', {
                        detail: { type: data.negative_marking_type || 'fixed' }
                    }));

                    window.dispatchEvent(new CustomEvent('set-import-toggle', {
                        detail: { checked: data.has_imported_questions ? true : false }
                    }));

                    // Translation & Language Logic
                    window.dispatchEvent(new CustomEvent('set-translation-toggle', {
                        detail: { checked: data.allow_translation ? true : false }
                    }));

                    const langSelect = document.getElementById('translationLanguageSelect');
                    if (langSelect) {
                        langSelect.value = data.translation_language || "";
                    }

                    previouslySelectedTopics = data.imported_topic_ids || [];

                    // Update dropdown with MicroCategory ID
                    if (data.micro_category_id) {
                         updateSkillsDropdown(data.micro_category_id);
                    }

                    if (data.imported_skill_ids && data.imported_skill_ids.length > 0) {
                        const firstSkillId = data.imported_skill_ids[0];
                        const skillData = allSkillsTopics[firstSkillId];
                        if (skillData) {
                            window.dispatchEvent(new CustomEvent('set-skill-dropdown', {
                                detail: { text: skillData.name, id: firstSkillId }
                            }));
                            selectSkill(firstSkillId, skillData.name, null);
                        }
                    }

                    toggleModal(true);
                })
                .catch(err => {
                    console.error('Edit Section Error:', err);
                    alert('Error loading section details: ' + err.message);
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initializeSkillsTopicsData();
        });
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
