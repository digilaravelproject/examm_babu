@php
    // --- Dynamic Route Logic ---
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

<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50/50">
                <tr>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Exam Details</th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Sections
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">
                        Topic
                    </th>

                    {{-- NEW: Creator Info (Admin Only) --}}
                    @if (auth()->user()->hasRole('admin'))
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Created By</th>
                    @endif

                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status
                    </th>
                    <th class="px-5 py-4 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($exams as $exam)
                    <tr class="transition-colors hover:bg-gray-50/80 group">

                        {{-- Title & Meta --}}
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span
                                    class="text-sm font-bold text-gray-900 transition-colors group-hover:text-[var(--brand-blue)]">
                                    {{ $exam->title }}
                                </span>
                                <div class="flex items-center gap-2 mt-1.5">
                                    {{-- Exam Code Badge using Sky & Blue --}}
                                    <span
                                        class="px-2 py-0.5 font-mono text-[10px] font-bold rounded border border-[var(--brand-sky)]"
                                        style="background-color: rgba(127, 210, 234, 0.1); color: var(--brand-blue);">
                                        {{ $exam->code }}
                                    </span>

                                    {{-- UPDATED META INFO: Type • Sub Cat • Micro Cat --}}
                                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-medium">
                                        {{ $exam->examType->name ?? 'N/A' }} •
                                        {{ $exam->subCategory->name ?? 'N/A' }} •
                                        {{ $exam->microCategory->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Sections Badge --}}
                        <td class="px-5 py-4 text-center">
                            <span
                                class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 border border-gray-200">
                                {{ $exam->exam_sections_count }} Sections
                            </span>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            @if ($exam->topic)
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold
                                                text-[var(--brand-blue)] bg-blue-50 border border-blue-100 rounded-full">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M17.707 9.293l-7-7A1 1 0 009.586 2H4a2 2 0 00-2 2v5.586a1 1 0 00.293.707l7 7a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" />
                                        </svg>
                                        {{ $exam->topic->name }}
                                    </span>
                                    @if($exam->topic->skill && $exam->topic->skill->microCategory)
                                        <div class="flex items-center gap-1 text-[10px] text-gray-400 pl-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <span class="font-medium">{{ $exam->topic->skill->microCategory->name }}</span>
                                            @if($exam->topic->skill->microCategory->subCategory)
                                                <span class="text-gray-300">›</span>
                                                <span>{{ $exam->topic->skill->microCategory->subCategory->name }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-400 bg-gray-100 rounded-full">
                                    No Topic
                                </span>
                            @endif
                        </td>

                        {{-- NEW: Creator Info (Admin Only) --}}
                        @if (auth()->user()->hasRole('admin'))
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">
                                        {{ $exam->creator ? $exam->creator->fullname : 'Unknown' }}
                                    </span>
                                    <span class="text-[10px] text-gray-500">
                                        @if ($exam->creator && $exam->creator->roles->isNotEmpty())
                                            {{ $exam->creator->roles->first()->name }}
                                        @else
                                            --
                                        @endif
                                    </span>
                                </div>
                            </td>
                        @endif

                        {{-- Status Badge (Updated Logic) --}}
                        <td class="px-5 py-4 text-center">
                            @php
                                $status = $exam->status ?? 'draft';
                                $statusClass = match ($status) {
                                    'published' => 'bg-green-100 text-green-700 border-green-200',
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-gray-100 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">

                                {{-- 🔥 APPROVAL WORKFLOW ACTIONS --}}
                               {{-- 🔥 APPROVAL WORKFLOW ACTIONS --}}
                                @if(auth()->user()->hasRole('instructor'))
                                    {{-- Instructor: Submit for Review --}}
                                    @if(in_array($exam->status, ['draft', 'rejected']))
                                        <button onclick="openSubmitModal('{{ $exam->id }}', '{{ $exam->title }}')"
                                            class="flex items-center justify-center h-8 px-3 text-xs font-bold text-white transition-all bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 whitespace-nowrap"
                                            title="Submit for Review">
                                            Submit for Review
                                        </button>
                                    @endif
                                @elseif(auth()->user()->hasRole('admin'))
                                    {{-- Admin: Approve/Reject --}}
                                    @if($exam->status === 'pending')
                                        {{-- Fixed Color: Changed to Purple to ensure visibility against white text --}}
                                        <button onclick="openReviewModal('{{ $exam->id }}', '{{ $exam->title }}', '{{ $exam->submitter_note }}')"
                                            class="flex items-center justify-center h-8 px-3 text-xs font-bold text-white transition-all bg-purple-600 rounded-lg shadow-sm hover:bg-purple-700 animate-pulse"
                                            title="Review Request">
                                            Review Request
                                        </button>
                                    @endif
                                @endif

                                {{-- Preview Button --}}
                                <a href="{{ route($routePrefix . 'exams.preview', array_merge($routeParams, ['exam' => $exam->id])) }}"
                                    target="_blank"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-emerald-500 hover:border-emerald-500 hover:text-white group/btn"
                                    title="Preview Exam Interface">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route($routePrefix . 'exams.edit', array_merge($routeParams, ['exam' => $exam->id])) }}"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:text-white group/btn"
                                    onmouseover="this.style.backgroundColor='var(--brand-blue)'; this.style.borderColor='var(--brand-blue)';"
                                    onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#e5e7eb';"
                                    title="Edit">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                {{-- Settings --}}
                                <a href="{{ route($routePrefix . 'exams.settings', array_merge($routeParams, ['exam' => $exam->id])) }}"
                                    class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-slate-800 hover:border-slate-800 hover:text-white group/btn"
                                    title="Settings">
                                    <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>

                                {{-- Duplicate --}}
                                <form method="POST"
                                    action="{{ route($routePrefix . 'exams.duplicate', array_merge($routeParams, ['exam' => $exam->id])) }}"
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to duplicate this exam?');">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-indigo-500 hover:border-indigo-500 hover:text-white group/btn"
                                        title="Duplicate Exam">
                                        <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2M16 8h2a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2" />
                                        </svg>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form
                                    action="{{ route($routePrefix . 'exams.destroy', array_merge($routeParams, ['exam' => $exam->id])) }}"
                                    method="POST" onsubmit="return confirm('Delete this exam?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-red-500 hover:border-red-500 hover:text-white group/btn"
                                        title="Delete">
                                        <svg class="w-4 h-4 text-gray-500 group-hover/btn:text-white" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
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
                        <td colspan="{{ auth()->user()->hasRole('admin') ? '8' : '7' }}"
                            class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 mb-4 rounded-full bg-gray-50">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-base font-semibold text-gray-900">No exams found</p>
                                <p class="text-sm text-gray-500">Try adjusting your filters or search terms.</p>
                                <button @click="search = ''; type = ''; status = ''; fetchExams()"
                                    class="mt-4 text-sm font-bold transition-colors hover:underline"
                                    style="color: var(--brand-blue);">
                                    Clear all filters
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($exams->hasPages())
        <div class="px-4 py-4 border-t border-gray-200 bg-gray-50/50 pagination-wrapper">
            {{-- FIX: Append Params to Pagination --}}
            {{ $exams->appends(array_merge($routeParams, request()->all()))->links() }}
        </div>
    @endif
</div>

{{-- MODALS FOR APPROVAL WORKFLOW --}}

{{-- 1. Instructor Submit Modal --}}
<div id="submitModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-900/60" onclick="closeSubmitModal()"></div>
        <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <h3 class="text-lg font-bold text-gray-900">Submit for Review</h3>
            <p class="mt-2 text-sm text-gray-500">Send <span id="submitExamTitle" class="font-bold text-gray-800"></span> to Admin for approval?</p>
            <form id="submitForm" method="POST">
                @csrf
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase">Note (Optional)</label>
                    <textarea name="submitter_note" rows="3" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-indigo-500" placeholder="e.g. Ready for publishing..."></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeSubmitModal()" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Admin Review Modal --}}
<div id="reviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-900/60" onclick="closeReviewModal()"></div>
        <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <h3 class="text-lg font-bold text-gray-900">Review Request</h3>
            <p class="mt-2 text-sm text-gray-500">Action for <span id="reviewExamTitle" class="font-bold text-gray-800"></span></p>

            <div class="p-3 mt-3 text-sm italic text-gray-600 border-l-4 border-indigo-400 rounded bg-gray-50">
                <strong>Instructor Note:</strong> <span id="reviewSubmitterNote"></span>
            </div>

            <form id="reviewForm" method="POST">
                @csrf
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase">Admin Note (Reason)</label>
                    <textarea name="admin_note" rows="3" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-indigo-500" placeholder="Reason for rejection or approval remarks..."></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeReviewModal()" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" name="action" value="reject" class="px-4 py-2 text-sm font-bold text-white bg-red-500 rounded-lg hover:bg-red-600">Reject</button>
                    <button type="submit" name="action" value="approve" class="px-4 py-2 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700">Approve & Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Instructor Submit Modal Logic
    function openSubmitModal(id, title) {
        document.getElementById('submitExamTitle').innerText = title;
        // FIX: Pass params array to route helper
        // Use PHP to generate valid base URL based on routeParams
        let url = "{{ route($routePrefix . 'exams.index', $routeParams) }}";

        // Remove query params if any from the generated URL before appending
        url = url.split('?')[0];

        // Construct the submit action URL
        // It converts /instructor/exams to /instructor/exams/{id}/submit-review
        // Or /admin/exams to /admin/exams/{id}/submit-review
        url = url.replace(/\/exams\/?$/, '/exams/' + id + '/submit-review');

        document.getElementById('submitForm').action = url;
        document.getElementById('submitModal').classList.remove('hidden');
    }
    function closeSubmitModal() {
        document.getElementById('submitModal').classList.add('hidden');
    }

    // Admin Review Modal Logic
    function openReviewModal(id, title, note) {
        document.getElementById('reviewExamTitle').innerText = title;
        document.getElementById('reviewSubmitterNote').innerText = note || 'No note provided.';
        // Admin route is static so this is safe
        // But let's use the dynamic variable just in case
        let url = "{{ route('admin.exams.index') }}";
        url = url.split('?')[0];
        url = url.replace(/\/exams\/?$/, '/exams/' + id + '/status-action');

        document.getElementById('reviewForm').action = url;
        document.getElementById('reviewModal').classList.remove('hidden');
    }
    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }
</script>
