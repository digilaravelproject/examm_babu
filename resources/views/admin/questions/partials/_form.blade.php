@php
    $isEdit = $question->exists;

    // -------------------------------------------------------------------------
    // 1. ROUTE & PERMISSION LOGIC
    // -------------------------------------------------------------------------
    if (!isset($routePrefix)) {
        $isAdmin = request()->routeIs('admin.*');
        $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    }

    if (!isset($routeParams)) {
        $routeParams = [];
        if (!request()->routeIs('admin.*')) {
            $routeParams = ['role' => request()->route('role') ?? 'instructor'];
        }
    }

    if ($isEdit) {
        $actionParams = array_merge($routeParams, ['question' => $question->id]);
        $action = route($routePrefix . 'questions.update', $actionParams);
    } else {
        $action = route($routePrefix . 'questions.store', $routeParams);
    }

    $typeCode = $questionType->code ?? $question->questionType->code;

    // -------------------------------------------------------------------------
    // 2. DATA INITIALIZATION (OPTIONS & ANSWERS)
    // -------------------------------------------------------------------------
    $currentOptions = old('options', $question->options ?? []);

    // Default structures based on Question Type
    if (empty($currentOptions)) {
        if ($typeCode == 'TOF') {
            $currentOptions = [
                ['option' => 'True', 'is_correct' => false],
                ['option' => 'False', 'is_correct' => false],
            ];
        } elseif ($typeCode == 'MTF') {
            $currentOptions = [['option' => '', 'pair' => ''], ['option' => '', 'pair' => '']];
        } elseif ($typeCode == 'FIB') {
            $currentOptions = []; // FIB uses question text regex
        } elseif ($typeCode == 'SAQ') {
            $currentOptions = [['option' => '']];
        } else {
            // Default 4 Options for MSA, MMA, ORD
            $currentOptions = [
                ['option' => '', 'image' => null, 'is_correct' => false],
                ['option' => '', 'image' => null, 'is_correct' => false],
                ['option' => '', 'image' => null, 'is_correct' => false],
                ['option' => '', 'image' => null, 'is_correct' => false],
            ];
        }
    }

    // Ensure array format
    if (is_string($currentOptions)) {
        $currentOptions = json_decode($currentOptions, true);
    }

    // Process Options: Fix Images, MMA Checkboxes & MTF Split Logic
    $currentOptions = collect($currentOptions)->map(function ($opt) {
        // Convert object to array if needed
        $opt = (array) $opt;

        // Image Preview Logic
        $opt['previewUrl'] = isset($opt['image']) && $opt['image'] ? asset('storage/' . $opt['image']) : null;

        // MMA Checkbox Logic
        $opt['is_correct'] =
            isset($opt['is_correct']) &&
            ($opt['is_correct'] == '1' || $opt['is_correct'] === true || $opt['is_correct'] == 'on')
                ? true
                : false;

        // --- FIX: MTF SPLIT LOGIC ---
        // Agar Option mein comma hai aur Pair khali hai, to usse split kar do
        if (
            isset($opt['option']) &&
            (empty($opt['pair']) || trim($opt['pair']) === '') &&
            strpos($opt['option'], ',') !== false
        ) {
            // Remove HTML tags temporarily to clean split if needed, but regex is safer
            // Simple explode for "Left, Right" format
            $parts = explode(',', $opt['option'], 2);
            $opt['option'] = trim($parts[0]); // Left Side
            $opt['pair'] = trim($parts[1]); // Right Side
        }

        return $opt;
    });

    $jsonOptions = json_encode($currentOptions);
    $correctAnswer = old('correct_answer', $question->correct_answer);
    $existingQImage = $question->question_image ? asset('storage/' . $question->question_image) : null;

    // -------------------------------------------------------------------------
    // 3. ERROR HANDLING FOR TABS
    // -------------------------------------------------------------------------
    $hasDetailsError = $errors->hasAny(['question', 'correct_answer', 'options']);
    $hasSettingsError = $errors->hasAny([
        'skill_id',
        'topic_id',
        'difficulty_level_id',
        'default_marks',
        'default_time',
    ]);
    $hasSolutionError = $errors->hasAny(['solution', 'hint', 'solution_video']);
    $hasAttachmentError = $errors->hasAny(['attachment_type', 'comprehension_id']);

    $initialTab = 'details';
    if ($hasSettingsError) {
        $initialTab = 'settings';
    }
    if ($hasSolutionError) {
        $initialTab = 'solution';
    }
    if ($hasAttachmentError) {
        $initialTab = 'attachment';
    }
    if ($hasDetailsError) {
        $initialTab = 'details';
    } // Priority
@endphp

{{-- =======================================================================
     CSS STYLES (INLINE)
     ======================================================================= --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<style>
    /* Form Layout */
    .form-label {
        display: block;
        font-weight: 700;
        color: #374151;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    /* Inputs */
    .custom-input,
    .custom-select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }

    .custom-input:focus,
    .custom-select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* Tabs */
    .tab-btn {
        position: relative;
        padding: 1rem;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
    }

    .tab-btn.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background-color: #eff6ff;
    }

    .tab-error-dot {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background-color: #ef4444;
        border-radius: 50%;
    }

    /* Buttons */
    .btn-primary {
        background-color: #2563eb;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: background 0.2s;
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
    }

    .btn-secondary {
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: background 0.2s;
    }

    .btn-secondary:hover {
        background-color: #e5e7eb;
    }

    /* Modals */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.75);
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }

    .modal-content {
        background-color: white;
        border-radius: 1rem;
        overflow: hidden;
        width: 100%;
        max-width: 56rem;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        padding: 1rem 1.5rem;
        background-color: #1f2937;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        flex: 1;
        overflow: auto;
        padding: 0;
        background-color: #000;
        position: relative;
    }

    .modal-footer {
        padding: 1rem;
        border-top: 1px solid #e5e7eb;
        background-color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Utilities */
    .tox-tinymce {
        border-radius: 0.5rem !important;
        border-color: #d1d5db !important;
    }

    .math-preview {
        padding: 1rem;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        min-height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    [x-cloak] {
        display: none !important;
    }
</style>

{{-- =======================================================================
     MAIN ALPINE COMPONENT
     ======================================================================= --}}
<div x-data="questionForm({
    typeCode: '{{ $typeCode }}',
    options: {{ $jsonOptions }},
    correctAnswer: '{{ is_array($correctAnswer) ? json_encode($correctAnswer) : $correctAnswer }}',
    activeTab: '{{ $initialTab }}',
    skills: {{ $skills }},
    allTopics: {{ $topics }},
    selectedSkill: '{{ old('skill_id', $question->skill_id) }}',
    hasAttachment: {{ old('has_attachment', $question->has_attachment ?? 0) ? 'true' : 'false' }},
    attachmentType: '{{ old('attachment_type', $question->attachment_type ?? 'comprehension') }}',
    solutionHasVideo: {{ !empty($question->solution_video) ? 'true' : 'false' }},
    questionImagePreview: '{{ $existingQImage }}'
})" class="bg-white border border-gray-200 shadow-xl rounded-xl overflow-hidden font-sans">

    {{-- FORM START --}}
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" @submit="syncEditors">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        {{-- Hidden Fields --}}
        <input type="hidden" name="question_type_id" value="{{ $questionType->id ?? $question->question_type_id }}">
        <input type="hidden" name="last_active_tab" x-model="activeTab">

        {{-- TABS HEADER --}}
        <div class="flex border-b border-gray-200 sticky top-0 bg-white z-10">
            <template x-for="tab in tabs">
                <div @click="activeTab = tab.id" :class="activeTab === tab.id ? 'active' : ''" class="tab-btn flex-1">
                    <span x-text="tab.label"></span>
                    <span x-show="hasError(tab.id)" class="tab-error-dot"></span>
                </div>
            </template>
        </div>

        <div class="p-6">
            {{-- Global Validation Alert --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-bold">Please correct the errors below.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ===========================================================
                 TAB 1: DETAILS (CONTENT & OPTIONS)
                 =========================================================== --}}
            <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200">

                {{-- QUESTION BODY --}}
                <div class="mb-8 space-y-4">
                    <div class="flex justify-between items-end border-b border-gray-100 pb-2">
                        <label class="form-label mb-0 uppercase tracking-wide">Question Text <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            {{-- Math Button --}}
                            <button type="button" @click="openMathModal('question')"
                                class="flex items-center gap-1 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-bold text-gray-700 transition">
                                <span>∑</span> Insert Math
                            </button>
                            {{-- Image Button --}}
                            <div class="relative">
                                <input type="file" name="question_image" id="q_image_input" class="hidden"
                                    accept="image/*" @change="handleImageSelection($event, 'question')">
                                <button type="button" @click="document.getElementById('q_image_input').click()"
                                    class="flex items-center gap-1 px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded text-xs font-bold text-blue-700 transition">
                                    📷 Add Image
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Question Image Preview --}}
                    <div x-show="questionImagePreview"
                        class="relative inline-block mt-2 border-2 border-gray-200 border-dashed p-1 rounded-lg">
                        <img :src="questionImagePreview" class="h-32 w-auto object-contain rounded">
                        <button type="button" @click="removeQuestionImage"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- TinyMCE Editor --}}
                    <textarea name="question" id="editor_question" class="w-full opacity-0">{{ old('question', $question->question) }}</textarea>
                    @error('question')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- OPTIONS SECTION --}}
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">
                            Answer Options
                            <span class="text-xs font-normal text-gray-500 ml-2"
                                x-text="'(' + typeCode + ' Mode)'"></span>
                        </h3>
                        <button type="button" x-show="options.length > 0 && typeCode !== 'FIB'"
                            @click="renderAllMath()" class="text-xs text-blue-600 font-bold hover:underline">
                            Refresh Math Logic ⟳
                        </button>
                    </div>

                    {{-- ==================== TYPE: MSA (Single Choice) ==================== --}}
                    <template x-if="typeCode === 'MSA'">
                        <div class="space-y-6">
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm relative">
                                    <div class="flex justify-between items-center mb-3">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" name="correct_answer" :value="index"
                                                x-model="correctAnswer"
                                                class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider"
                                                x-text="'Option ' + (index + 1)"></span>
                                            <span x-show="correctAnswer == index"
                                                class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-0.5 rounded">CORRECT</span>
                                        </label>
                                        <div class="flex gap-2">
                                            <button type="button" @click="openMathModal(index)"
                                                class="text-gray-400 hover:text-blue-600">∑ Math</button>
                                            <button type="button" @click="removeOption(index)"
                                                class="text-red-400 hover:text-red-600 font-bold text-xs">REMOVE</button>
                                        </div>
                                    </div>
                                    <div class="min-h-[250px]">
                                        <textarea :id="'editor_opt_' + index" :name="'options[' + index + '][option]'" class="w-full"></textarea>
                                    </div>
                                                                        {{-- Option Image Upload UI --}}
                                    <div class="mt-3 flex items-center gap-3 border-t pt-3">
                                       <div class="relative">
    <input type="file" 
           :name="'options[' + index + '][image]'"
           :id="'opt_img_' + index" 
           class="hidden" 
           accept="image/*"
           @change="handleImageSelection($event, index)">
           
    <button type="button"
            @click="$el.previousElementSibling.click()" 
            class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-bold text-gray-700 transition">
        <span>📷</span> Add Image
    </button>
</div>
<div x-show="opt.previewUrl" class="relative inline-block border p-1 rounded bg-gray-50">
    <img :src="opt.previewUrl" class="h-10 w-10 object-contain">
    <button type="button" @click="removeImage(index)"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow">×</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-500 font-bold rounded-lg hover:border-blue-400 hover:text-blue-500 transition">
                                + Add Option
                            </button>
                        </div>
                    </template>

                    {{-- ==================== TYPE: MMA (Multiple Choice) ==================== --}}
                    <template x-if="typeCode === 'MMA' || typeCode === 'MMS'">
                        <div class="space-y-6">
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" :name="'options[' + index + '][is_correct]'"
                                                value="1" x-model="opt.is_correct"
                                                class="w-5 h-5 text-green-600 rounded focus:ring-green-500 border-gray-300">
                                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider"
                                                x-text="'Option ' + (index + 1)"></span>
                                            <span x-show="opt.is_correct"
                                                class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-0.5 rounded">CORRECT</span>
                                        </label>
                                        <button type="button" @click="removeOption(index)"
                                            class="text-red-400 hover:text-red-600 font-bold text-xs">REMOVE</button>
                                    </div>
                                    <div class="min-h-[250px]">
                                        <textarea :id="'editor_opt_' + index" :name="'options[' + index + '][option]'" class="w-full"></textarea>
                                    </div>

                                    {{-- Option Image Upload UI --}}
                                    <div class="mt-3 flex items-center gap-3 border-t pt-3">
                                        <div class="relative">
                                            <input type="file" :name="'options[' + index + '][image]'"
                                                :id="'opt_img_' + index" class="hidden" accept="image/*"
                                                @change="handleImageSelection($event, index)">
                                            <button type="button"
                                                @click="document.getElementById('opt_img_'+index).click()"
                                                class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-bold text-gray-700 transition">
                                                <span>📷</span> Add Image
                                            </button>
                                        </div>
                                        <div x-show="opt.previewUrl"
                                            class="relative inline-block border p-1 rounded bg-gray-50">
                                            <img :src="opt.previewUrl" class="h-10 w-10 object-contain">
                                            <button type="button" @click="removeImage(index)"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow">×</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-500 font-bold rounded-lg hover:border-green-400 hover:text-green-500 transition">
                                + Add Checkbox Option
                            </button>
                        </div>
                    </template>

                    {{-- ==================== TYPE: MTF (Match Pairs) ==================== --}}
                    <template x-if="typeCode === 'MTF'">
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4 mb-2 px-1">
                                <span class="text-xs font-bold text-gray-400 uppercase">Left Column (Question)</span>
                                <span class="text-xs font-bold text-gray-400 uppercase">Right Column (Answer
                                    Pair)</span>
                            </div>
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="relative bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {{-- Left Editor --}}
                                        <div class="min-h-[250px]">
                                            <textarea :id="'editor_opt_left_' + index" :name="'options[' + index + '][option]'" class="w-full"></textarea>
                                        </div>
                                        {{-- Right Editor --}}
                                        <div class="min-h-[250px]">
                                            <textarea :id="'editor_opt_right_' + index" :name="'options[' + index + '][pair]'" class="w-full"></textarea>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 border border-red-200 hover:bg-red-600 hover:text-white transition shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addPair()"
                                class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-500 font-bold rounded-lg hover:border-indigo-400 hover:text-indigo-500 transition">
                                + Add Match Pair
                            </button>
                        </div>
                    </template>

                    {{-- ==================== TYPE: ORD (Ordering) ==================== --}}
                    <template x-if="typeCode === 'ORD'">
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-gray-700">Set Correct Order (Top to Bottom)</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="flex items-start gap-4 bg-white p-4 border rounded-lg shadow-sm">
                                    <div class="w-8 h-8 bg-gray-800 text-white rounded-md flex items-center justify-center font-bold flex-shrink-0"
                                        x-text="index + 1"></div>
                                    <div class="flex-1 min-h-[150px]">
                                        <textarea :id="'editor_opt_' + index" :name="'options[' + index + '][option]'" class="w-full"></textarea>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="text-red-400 hover:text-red-600 pt-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-500 font-bold rounded-lg hover:bg-gray-50">+
                                Add Step</button>
                        </div>
                    </template>

                    {{-- ==================== TYPE: FIB (Fill in Blanks) ==================== --}}
                    <template x-if="typeCode === 'FIB'">
                        <div class="p-6 border border-blue-200 rounded-lg bg-blue-50">
                            <h4 class="font-bold text-blue-900">Instructions</h4>
                            <p class="mt-2 text-sm text-blue-700">Wrap words with double hashes <code>##</code>. E.g.
                                <strong>##Answer##</strong>.
                            </p>
                        </div>
                    </template>

                    {{-- ==================== TYPE: TOF (True/False) ==================== --}}
                    <template x-if="typeCode === 'TOF'">
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="(opt, index) in options" :key="index">
                                <label
                                    class="relative flex items-center justify-center p-6 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition-all"
                                    :class="correctAnswer == index ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'">
                                    <input type="radio" name="correct_answer" :value="index"
                                        x-model="correctAnswer" class="sr-only">
                                    <span class="text-2xl font-bold uppercase"
                                        :class="correctAnswer == index ? 'text-blue-700' : 'text-gray-400'"
                                        x-text="opt.option"></span>
                                    <input type="hidden" :name="'options[' + index + '][option]'"
                                        :value="opt.option">
                                    <div x-show="correctAnswer == index" class="absolute top-2 right-2 text-blue-500">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </template>

                    {{-- ==================== TYPE: SAQ (Short Answer) ==================== --}}
                    <template x-if="typeCode === 'SAQ'">
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-gray-700">Accepted Answers (Case Insensitive)</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="flex gap-2">
                                    <input type="text" :name="'options[' + index + '][option]'" x-model="opt.option"
                                        class="custom-input" placeholder="Type an accepted answer...">
                                    <button type="button" @click="removeOption(index)"
                                        class="bg-red-100 text-red-600 px-4 rounded-lg font-bold hover:bg-red-200">×</button>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="text-sm text-blue-600 font-bold hover:underline">+ Add Another Accepted
                                Answer</button>
                        </div>
                    </template>

                </div>

                {{-- Action Footer --}}
                <div class="flex justify-end border-t border-gray-100 pt-6">
                    <button type="button" @click="activeTab = 'settings'" class="btn-primary">
                        Next: Settings &rarr;
                    </button>
                </div>
            </div>

            {{-- ===========================================================
                 TAB 2: SETTINGS (MARKS, TIME, TAXONOMY)
                 =========================================================== --}}
            <div x-show="activeTab === 'settings'" class="space-y-8"
                x-transition:enter="transition ease-out duration-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="form-label">Skill Category <span class="text-red-500">*</span></label>
                        <select name="skill_id" x-model="selectedSkill" @change="filterTopics()"
                            class="custom-select" :class="{ 'border-red-500': errors.settings }">
                            <option value="">-- Select Skill --</option>
                            <template x-for="skill in skills" :key="skill.id">
                                <option :value="skill.id" x-text="skill.name"
                                    :selected="skill.id == selectedSkill"></option>
                            </template>
                        </select>
                        @error('skill_id')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Topic / Sub-Skill</label>
                        <select name="topic_id" class="custom-select">
                            <option value="">-- Select Topic --</option>
                            <template x-for="topic in availableTopics" :key="topic.id">
                                <option :value="topic.id" x-text="topic.name"
                                    :selected="topic.id == '{{ $question->topic_id }}'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 rounded-xl border border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="form-label">Difficulty Level</label>
                        <select name="difficulty_level_id" class="custom-select">
                            @foreach ($difficultyLevels as $level)
                                <option value="{{ $level->id }}"
                                    {{ old('difficulty_level_id', $question->difficulty_level_id) == $level->id ? 'selected' : '' }}>
                                    {{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Default Marks (+ve)</label>
                        <input type="number" step="0.25" name="default_marks"
                            value="{{ old('default_marks', $question->default_marks ?? 1) }}"
                            class="custom-input font-bold text-gray-700">
                        @error('default_marks')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Time Limit (Seconds)</label>
                        <input type="number" name="default_time"
                            value="{{ old('default_time', $question->default_time ?? 60) }}"
                            class="custom-input font-bold text-gray-700">
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 pt-6">
                    <button type="button" @click="activeTab = 'details'" class="btn-secondary">&larr; Back</button>
                    <button type="button" @click="activeTab = 'solution'" class="btn-primary">Next: Solution
                        &rarr;</button>
                </div>
            </div>

            {{-- ===========================================================
                 TAB 3: SOLUTION (EXPLANATION & VIDEO)
                 =========================================================== --}}
            <div x-show="activeTab === 'solution'" class="space-y-8"
                x-transition:enter="transition ease-out duration-200">
                <div class="space-y-4">
                    <label class="form-label text-green-700 uppercase tracking-wide">Detailed Solution
                        Explanation</label>
                    <textarea name="solution" id="editor_solution" class="opacity-0">{{ old('solution', $question->solution) }}</textarea>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="form-label mb-0">Video Solution (YouTube)</label>
                        <div class="flex bg-white rounded border overflow-hidden">
                            <button type="button" @click="solutionHasVideo = true"
                                :class="solutionHasVideo ? 'bg-red-500 text-white' : 'text-gray-500'"
                                class="px-3 py-1 text-xs font-bold transition">YES</button>
                            <button type="button" @click="solutionHasVideo = false"
                                :class="!solutionHasVideo ? 'bg-gray-200 text-gray-700' : 'text-gray-500'"
                                class="px-3 py-1 text-xs font-bold transition">NO</button>
                        </div>
                    </div>
                    <div x-show="solutionHasVideo" x-transition>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                                </svg>
                            </span>
                            <input type="url" name="solution_video" x-model="videoUrl"
                                value="{{ old('solution_video', $question->solution_video) }}"
                                class="custom-input pl-10" placeholder="Paste YouTube URL here...">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Example: https://www.youtube.com/watch?v=dQw4w9WgXcQ</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="form-label text-gray-500">Hint (Optional)</label>
                    <textarea name="hint" id="editor_hint" class="opacity-0">{{ old('hint', $question->hint) }}</textarea>
                </div>

                <div class="flex justify-between border-t border-gray-100 pt-6">
                    <button type="button" @click="activeTab = 'settings'" class="btn-secondary">&larr; Back</button>
                    <button type="button" @click="activeTab = 'attachment'" class="btn-primary">Next: Attachments
                        &rarr;</button>
                </div>
            </div>

            {{-- ===========================================================
                 TAB 4: ATTACHMENTS (PASSAGE & MEDIA)
                 =========================================================== --}}
            <div x-show="activeTab === 'attachment'" class="space-y-8"
                x-transition:enter="transition ease-out duration-200">
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-indigo-900">Enable Attachments</h4>
                        <p class="text-sm text-indigo-600">Link a comprehension passage or media file to this question.
                        </p>
                    </div>

                    <div
                        class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="has_attachment" id="toggle_attachment" value="1"
                            x-model="hasAttachment"
                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200"
                            :class="hasAttachment ? 'translate-x-6' : 'translate-x-0'" />
                        <label for="toggle_attachment"
                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-200"
                            :class="hasAttachment ? 'bg-indigo-500' : 'bg-gray-300'"></label>
                    </div>

                </div>

                <div x-show="hasAttachment" x-transition class="p-6 border border-gray-200 rounded-xl space-y-6">
                    <div>
                        <label class="form-label">Attachment Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="attachment_type" value="comprehension"
                                    x-model="attachmentType" class="text-indigo-600">
                                <span class="font-bold text-gray-700">Comprehension Passage</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="attachment_type" value="audio" x-model="attachmentType"
                                    class="text-indigo-600">
                                <span class="font-bold text-gray-700">Audio File</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="attachment_type" value="video" x-model="attachmentType"
                                    class="text-indigo-600">
                                <span class="font-bold text-gray-700">Video File</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="attachmentType === 'comprehension'" class="space-y-3">
                        <label class="form-label">Select Passage</label>
                        <select name="comprehension_id" class="custom-select">
                            <option value="">-- Choose a Passage --</option>
                            @foreach ($passages as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('comprehension_passage_id', $question->comprehension_passage_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">Don't see the passage? Create it in the Comprehension module
                            first.</p>
                    </div>

                    <div x-show="attachmentType === 'audio' || attachmentType === 'video'" class="space-y-3">
                        <label class="form-label">Media URL / ID</label>
                        <input type="text" name="attachment_options[link]"
                            value="{{ old('attachment_options.link', data_get($question->attachment_options, 'link')) }}"
                            class="custom-input" placeholder="e.g., https://example.com/audio.mp3">
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 pt-6">
                    <button type="button" @click="activeTab = 'solution'" class="btn-secondary">&larr; Back</button>
                    <button type="submit"
                        class="btn-submit btn px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg shadow-green-200 transition duration-200 ease-in-out">{{ $isEdit ? 'UPDATE QUESTION' : 'SAVE & UPLOAD QUESTION' }}</button>

                </div>
            </div>

        </div>
    </form>

    {{-- =======================================================================
         INLINE MODALS (No Includes)
         ======================================================================= --}}

    {{-- 1. CROP MODAL --}}
    <div x-show="showCropModal" style="display: none;" class="modal-backdrop" x-transition.opacity>
        <div class="modal-content" @click.outside="closeCropModal()">
            <div class="modal-header">
                <h3 class="text-lg font-bold">Crop Image</h3>
                <button @click="closeCropModal()" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <div class="modal-body h-[60vh] bg-black flex items-center justify-center">
                <img id="crop-image-element" style="max-width: 100%; max-height: 100%; display: block;">
            </div>
            <div class="modal-footer">
                <div class="flex gap-2">
                    <button type="button" @click="cropper.rotate(-90)" class="btn-secondary px-3 py-1 text-xs">↺
                        Left</button>
                    <button type="button" @click="cropper.rotate(90)" class="btn-secondary px-3 py-1 text-xs">↻
                        Right</button>
                    <button type="button" @click="cropper.reset()"
                        class="btn-secondary px-3 py-1 text-xs">Reset</button>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="closeCropModal()" class="btn-secondary">Cancel</button>
                    <button type="button" @click="performCrop()" class="btn-primary">Crop & Use</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MATH MODAL --}}
    <div x-show="showMathModal" style="display: none;" class="modal-backdrop" x-transition.opacity>
        <div class="modal-content max-w-lg" @click.outside="closeMathModal()">
            <div class="modal-header bg-[#0777be]">
                <h3 class="text-lg font-bold">Insert LaTeX Math</h3>
                <button @click="closeMathModal()" class="text-white/80 hover:text-white text-2xl">&times;</button>
            </div>
            <div class="p-6 bg-white space-y-4">
                <div>
                    <label class="form-label text-xs uppercase">LaTeX Expression</label>
                    <textarea x-model="mathInput" @input="updateMathPreview"
                        class="w-full h-32 border border-gray-300 rounded-lg p-3 font-mono text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="\frac{-b \pm \sqrt{b^2-4ac}}{2a}"></textarea>
                </div>
                <div>
                    <label class="form-label text-xs uppercase">Live Preview</label>
                    <div id="math-preview-target" class="math-preview"></div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50">
                <button type="button" @click="closeMathModal()" class="btn-secondary">Cancel</button>
                <button type="button" @click="insertMath()" class="btn-primary">Insert Formula</button>
            </div>
        </div>
    </div>

</div>

{{-- =======================================================================
     SCRIPTS
     ======================================================================= --}}
<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.1.0/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    // TinyMCE Configuration (Height increased to 250px)
    const getTinyConfig = (h = 250) => ({
        height: h,
        menubar: false,
        plugins: 'lists link image charmap preview anchor emoticons code table searchreplace visualblocks',
        toolbar: 'bold italic underline | bullist numlist | table link image emoticons | code removeformat',
        content_style: 'body { font-family:Inter,sans-serif; font-size:14px; padding: 10px 15px; color: #374151; } img { max-width: 100%; height: auto; border-radius: 4px; }',
        branding: false,
        setup: (editor) => {
            editor.on('change keyup', () => editor.save());
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('questionForm', (config) => ({
            // Data Properties
            typeCode: config.typeCode,
            options: config.options,
            correctAnswer: config.correctAnswer,
            activeTab: config.activeTab,
            skills: config.skills,
            allTopics: config.allTopics,
            availableTopics: [],
            selectedSkill: config.selectedSkill,
            hasAttachment: config.hasAttachment,
            attachmentType: config.attachmentType,
            solutionHasVideo: config.solutionHasVideo,
            videoUrl: '',

            // UI States
            showCropModal: false,
            cropper: null,
            cropContext: null,
            cropImageSrc: '',
            showMathModal: false,
            mathInput: '',
            activeMathContext: null,
            questionImagePreview: config.questionImagePreview,

            // Tabs
            tabs: [{
                    id: 'details',
                    label: '1. Content Details'
                },
                {
                    id: 'settings',
                    label: '2. Settings & Marks'
                },
                {
                    id: 'solution',
                    label: '3. Solution & Hint'
                },
                {
                    id: 'attachment',
                    label: '4. Attachments'
                }
            ],

            errors: {
                details: {{ $hasDetailsError ? 'true' : 'false' }},
                settings: {{ $hasSettingsError ? 'true' : 'false' }},
                solution: {{ $hasSolutionError ? 'true' : 'false' }},
                attachment: {{ $hasAttachmentError ? 'true' : 'false' }}
            },

            // --- INIT ---
            init() {
                this.filterTopics();
                this.$nextTick(() => {
                    // Initialize Static Editors
                    tinymce.init({
                        selector: '#editor_question',
                        ...getTinyConfig(400)
                    });
                    tinymce.init({
                        selector: '#editor_solution',
                        ...getTinyConfig(300)
                    });
                    tinymce.init({
                        selector: '#editor_hint',
                        ...getTinyConfig(200)
                    });

                    // Initialize Dynamic Editors
                    this.initAllOptionEditors();
                });
            },

            // --- TinyMCE Dynamic Management ---
            initAllOptionEditors() {
                this.$nextTick(() => {
                    this.options.forEach((opt, idx) => {
                        if (this.typeCode === 'MTF') {
                            this.bindTiny('editor_opt_left_' + idx, opt.option, (
                                val) => this.options[idx].option = val);
                            this.bindTiny('editor_opt_right_' + idx, opt.pair, (
                                val) => this.options[idx].pair = val);
                        } else if (!['FIB', 'SAQ', 'TOF'].includes(this.typeCode)) {
                            this.bindTiny('editor_opt_' + idx, opt.option, (val) =>
                                this.options[idx].option = val);
                        }
                    });
                });
            },

            bindTiny(id, content, callback) {
                // Ensure element exists and isn't already initialized
                const el = document.getElementById(id);
                if (el && !tinymce.get(id)) {
                    tinymce.init({
                        selector: '#' + id,
                        ...getTinyConfig(250), // Height set to 250px per request
                        setup: (editor) => {
                            editor.on('init', () => editor.setContent(content || ''));
                            editor.on('change keyup', () => callback(editor
                            .getContent()));
                        }
                    });
                }
            },

            syncEditors() {
                tinymce.triggerSave();
            },

            // --- Form Logic ---
            filterTopics() {
                this.availableTopics = this.allTopics.filter(t => t.skill_id == this.selectedSkill);
            },

            hasError(tabId) {
                return this.errors[tabId];
            },

            // --- Option CRUD ---
            addOption() {
                this.options.push({
                    option: '',
                    image: null,
                    is_correct: false,
                    previewUrl: null
                });
                this.initAllOptionEditors();
            },

            addPair() {
                this.options.push({
                    option: '',
                    pair: ''
                });
                this.initAllOptionEditors();
            },

            removeOption(index) {
                // Cleanup TinyMCE instances before removing from DOM
                const prefixes = ['editor_opt_', 'editor_opt_left_', 'editor_opt_right_'];
                prefixes.forEach(p => {
                    const inst = tinymce.get(p + index);
                    if (inst) inst.remove();
                });

                this.options.splice(index, 1);

                // Re-bind remaining editors after a slight delay to allow DOM update
                setTimeout(() => this.initAllOptionEditors(), 100);
            },

            removeImage(index) {
                this.options[index].image = null;
                this.options[index].previewUrl = null;
                const input = document.getElementById('opt_img_' + index);
                if (input) input.value = '';
            },

            // --- Image Cropper Logic ---
            handleImageSelection(event, context) {
                const file = event.target.files[0];
                if (!file) return;

                this.cropContext = context;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.cropImageSrc = e.target.result;
                    this.showCropModal = true;
                    this.$nextTick(() => {
                        const img = document.getElementById('crop-image-element');
                        img.src = this.cropImageSrc;
                        if (this.cropper) this.cropper.destroy();
                        this.cropper = new Cropper(img, {
                            viewMode: 2,
                            autoCropArea: 1,
                            responsive: true
                        });
                    });
                };
                reader.readAsDataURL(file);
                // Clear value to allow re-selecting same file
                event.target.value = '';
            },

            performCrop() {
                if (!this.cropper) return;

                this.cropper.getCroppedCanvas({
                    maxWidth: 1200,
                    maxHeight: 1200,
                    fillColor: '#fff'
                }).toBlob((blob) => {
                    const url = URL.createObjectURL(blob);
                    const file = new File([blob], "cropped_image.jpg", {
                        type: "image/jpeg"
                    });
                    const dt = new DataTransfer();
                    dt.items.add(file);

                    if (this.cropContext === 'question') {
                        this.questionImagePreview = url;
                        document.getElementById('q_image_input').files = dt.files;
                    } else {
                        // Option Context (index)
                        this.options[this.cropContext].previewUrl = url;
                        const input = document.getElementById('opt_img_' + this
                        .cropContext);
                        if (input) input.files = dt.files;
                    }
                    this.closeCropModal();
                }, 'image/jpeg', 0.85);
            },

            closeCropModal() {
                this.showCropModal = false;
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            removeQuestionImage() {
                this.questionImagePreview = null;
                document.getElementById('q_image_input').value = '';
            },

            // --- Math Logic ---
            openMathModal(context) {
                this.activeMathContext = context;
                this.mathInput = '';
                document.getElementById('math-preview-target').innerHTML = '';
                this.showMathModal = true;
            },

            closeMathModal() {
                this.showMathModal = false;
            },

            updateMathPreview() {
                const p = document.getElementById('math-preview-target');
                p.innerHTML = '\\(' + this.mathInput + '\\)';
                if (window.MathJax) MathJax.typesetPromise([p]);
            },

            insertMath() {
                const formula = ' \\(' + this.mathInput + '\\) ';
                let editorId = 'editor_question';

                if (this.activeMathContext !== 'question') {
                    if (this.typeCode === 'MTF') {
                        editorId = 'editor_opt_left_' + this
                        .activeMathContext; // Default to left, can enhance to choose
                    } else {
                        editorId = 'editor_opt_' + this.activeMathContext;
                    }
                }

                const editor = tinymce.get(editorId);
                if (editor) {
                    editor.insertContent(formula);
                } else {
                    alert('Editor not found. Please click inside the editor first.');
                }
                this.closeMathModal();
            }
        }));
    });
</script>
