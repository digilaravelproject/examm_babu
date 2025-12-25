@extends('layouts.admin')

@section('content')
<div class="max-w-6xl py-6 mx-auto">

    {{-- 1. Steps Navigation --}}
    @include('admin.exams.partials._steps', ['activeStep' => 'sections'])

    <div class="mt-6 space-y-6">

        {{-- 2. Header & Add Button --}}
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Manage Sections</h2>
                <p class="text-sm text-gray-500">Configure sections for <strong>{{ $exam->title }}</strong></p>
            </div>
            <button type="button" onclick="openAddModal()"
                class="flex items-center gap-2 px-6 py-2.5 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow-md hover:bg-[#0666a3] hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Section
            </button>
        </div>

        {{-- 3. Data Table --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">#</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Display Name</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Section</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Total Questions</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Total Duration</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Total Marks</th>
                            <th class="px-6 py-4 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($exam->examSections as $section)
                        <tr class="transition-colors hover:bg-gray-50 group">
                            {{-- Order --}}
                            <td class="px-6 py-4 text-gray-400 font-bold">
                                {{ $section->section_order }}
                            </td>

                            {{-- Display Name --}}
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800">{{ $section->name }}</span>
                            </td>

                            {{-- Section Type --}}
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg border border-blue-100">
                                    {{ $section->section->name ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Total Questions (Using Count Only) --}}
                            <td class="px-6 py-4 text-center">
                                <span class="font-mono text-sm font-bold text-gray-700">
                                    {{ $section->questions_count }} Q
                                </span>
                            </td>

                            {{-- Duration --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-gray-700">{{ floor($section->total_duration / 60) }} Mins</span>
                                    @if($exam->settings['auto_duration'] ?? true)
                                        <span class="text-[10px] text-green-600 font-medium bg-green-50 px-1 rounded">AUTO</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Marks --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-gray-700">{{ $section->total_marks }}</span>
                                    @if($exam->settings['auto_grading'] ?? true)
                                        <span class="text-[10px] text-green-600 font-medium bg-green-50 px-1 rounded">AUTO</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Edit --}}
                                    <button onclick="editSection({{ $section->id }})"
                                        class="p-2 text-gray-400 transition-all rounded-lg hover:text-[#0777be] hover:bg-blue-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.exams.sections.destroy', [$exam->id, $section->id]) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this section? All associated questions will be removed.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 transition-all rounded-lg hover:text-red-600 hover:bg-red-50">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <span class="text-sm font-medium">No sections added yet.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Next Button --}}
        <div class="flex justify-end mt-6 gap-3">
    {{-- Optional: "Finish Later" button --}}
    <a href="{{ route('admin.exams.index') }}" class="px-6 py-3 font-bold text-gray-500 transition bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-gray-700">
        Save & Exit
    </a>

    {{-- Main "Next Step" Button --}}
    @if($exam->examSections->count() > 0)
        <a href="{{ route('admin.exams.questions.index', $exam->id) }}" class="flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow-md hover:bg-[#0666a3] hover:shadow-lg">
            <span>Next: Add Questions</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
    @else
        <button disabled class="flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-gray-300 cursor-not-allowed rounded-xl">
            <span>Next: Add Questions</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
        <p class="mt-2 text-xs text-center text-red-500 w-full md:w-auto">Please add at least one section to proceed.</p>
    @endif
</div>
    </div>
</div>

{{-- 4. MODAL --}}
<div id="sectionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 backdrop-blur-sm" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="sectionForm" method="POST">
                @csrf
                <div id="methodField"></div>

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Add Section</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                        {{-- Name --}}
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase">Display Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]" placeholder="e.g. Logical Reasoning">
                        </div>

                        {{-- Section Type --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Section Type <span class="text-red-500">*</span></label>
                            <select name="section_id" required class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                                <option value="">Select...</option>
                                @foreach($availableSections as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Order --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Order <span class="text-red-500">*</span></label>
                            <input type="number" name="section_order" required class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]" value="{{ $exam->examSections->count() + 1 }}">
                        </div>

                        {{-- Correct Marks --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Correct Marks <span class="text-red-500">*</span></label>
                            <input type="number" name="correct_marks" step="0.25" required class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                        </div>

                        {{-- Negative Marks --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Negative Marks</label>
                            <div class="flex gap-2">
                                <select name="negative_marking_type" class="w-1/3 mt-1 text-xs border-gray-300 rounded-lg">
                                    <option value="fixed">Fixed</option>
                                    <option value="percentage">%</option>
                                </select>
                                <input type="number" name="negative_marks" step="0.25" class="w-2/3 mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]" value="0">
                            </div>
                        </div>

                        {{-- Cutoff --}}
                        @if($exam->settings['enable_section_cutoff'] ?? false)
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Cutoff (%)</label>
                            <input type="number" name="section_cutoff" step="1" max="100" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]" value="0">
                        </div>
                        @endif

                        {{-- Duration (Only if Manual) --}}
                        @if(!($exam->settings['auto_duration'] ?? true))
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Duration (Minutes) <span class="text-red-500">*</span></label>
                            <input type="number" name="total_duration" required class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                        </div>
                        @endif
                    </div>

                    {{-- Auto Info --}}
                    @if(($exam->settings['auto_duration'] ?? true) || ($exam->settings['auto_grading'] ?? true))
                    <div class="flex items-start gap-2 p-3 mt-6 text-xs text-blue-700 border border-blue-100 bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <strong>Note:</strong>
                            {{ ($exam->settings['auto_duration'] ?? true) ? 'Duration is calculated automatically based on questions.' : '' }}
                            {{ ($exam->settings['auto_grading'] ?? true) ? 'Total Marks are calculated automatically based on questions.' : '' }}
                        </div>
                    </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 font-bold text-white bg-[#0777be] rounded-lg shadow-md hover:bg-[#0666a3]">Save Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('sectionModal');
    const form = document.getElementById('sectionForm');
    const examId = "{{ $exam->id }}";

    function toggleModal(show) {
        if(show) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    function closeModal() {
        toggleModal(false);
    }

    function openAddModal() {
        form.reset();
        document.getElementById('modalTitle').innerText = "Add New Section";
        document.getElementById('methodField').innerHTML = "";
        form.action = `/admin/exams/${examId}/sections`;
        toggleModal(true);
    }

    function editSection(id) {
        fetch(`/admin/exams/${examId}/sections/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalTitle').innerText = "Edit Section";
                document.getElementById('methodField').innerHTML = '@method("PUT")';
                form.action = `/admin/exams/${examId}/sections/${id}`;

                // Populate Fields
                if(form.querySelector('[name="name"]')) form.querySelector('[name="name"]').value = data.name;
                if(form.querySelector('[name="section_id"]')) form.querySelector('[name="section_id"]').value = data.section_id;
                if(form.querySelector('[name="section_order"]')) form.querySelector('[name="section_order"]').value = data.section_order;
                if(form.querySelector('[name="correct_marks"]')) form.querySelector('[name="correct_marks"]').value = data.correct_marks;
                if(form.querySelector('[name="negative_marks"]')) form.querySelector('[name="negative_marks"]').value = data.negative_marks;
                if(form.querySelector('[name="negative_marking_type"]')) form.querySelector('[name="negative_marking_type"]').value = data.negative_marking_type;
                if(form.querySelector('[name="section_cutoff"]')) form.querySelector('[name="section_cutoff"]').value = data.section_cutoff;

                // Duration (if manual)
                if(form.querySelector('[name="total_duration"]')) {
                    form.querySelector('[name="total_duration"]').value = data.total_duration_minutes;
                }

                toggleModal(true);
            })
            .catch(err => {
                console.error(err);
                alert('Error loading section details.');
            });
    }
</script>
@endsection
