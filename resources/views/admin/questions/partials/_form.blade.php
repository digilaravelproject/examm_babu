@php
    $isEdit = $question->exists;
    $routePrefix = request()->routeIs('instructor.*') ? 'instructor.' : 'admin.';
    $action = $isEdit
        ? route($routePrefix . 'questions.update', $question->id)
        : route($routePrefix . 'questions.store');

    $typeCode = $questionType->code ?? $question->questionType->code;

    // Default Options Logic
    $currentOptions = old('options', $question->options ?? []);
    if (empty($currentOptions)) {
        if ($typeCode == 'TOF') {
            $currentOptions = [
                ['option' => 'True', 'is_correct' => false],
                ['option' => 'False', 'is_correct' => false],
            ];
        } elseif ($typeCode == 'MTF') {
            $currentOptions = [['option' => '', 'pair' => ''], ['option' => '', 'pair' => '']];
        } elseif ($typeCode == 'FIB') {
            $currentOptions = [];
        } else {
            $currentOptions = [
                ['option' => '', 'image' => null, 'is_correct' => false],
                ['option' => '', 'image' => null, 'is_correct' => false],
            ];
        }
    }

    $jsonOptions = json_encode($currentOptions);
    $correctAnswer = old('correct_answer', $question->correct_answer);

    // Tab Error Detection (For Red Dots)
    $hasSettingsError = $errors->hasAny([
        'skill_id',
        'topic_id',
        'difficulty_level_id',
        'default_marks',
        'default_time',
    ]);
    $hasSolutionError = $errors->hasAny(['solution', 'hint', 'solution_video']);
    $hasAttachmentError = $errors->hasAny(['attachment_type', 'comprehension_id']);

    // Initial Tab: If error, stay on error tab. Else start at Details.
    $initialTab = $hasSettingsError
        ? 'settings'
        : ($hasSolutionError
            ? 'solution'
            : ($hasAttachmentError
                ? 'attachment'
                : old('last_active_tab', 'details')));
@endphp

{{-- CSS --}}
<style>
    .form-label {
        display: block;
        font-weight: 700;
        color: #374151;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .custom-select {
        appearance: none;
        background-color: #fff;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        width: 100%;
        font-size: 0.875rem;
        line-height: 1.25;
        transition: all 0.2s;
    }

    .custom-select:focus {
        outline: none;
        border-color: #0777be;
        ring: 2px solid rgba(7, 119, 190, 0.2);
    }

    .custom-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .custom-input:focus {
        outline: none;
        border-color: #0777be;
        box-shadow: 0 0 0 3px rgba(7, 119, 190, 0.1);
    }

    /* Error Styles */
    .error-box {
        background-color: #fee2e2;
        border-left: 4px solid #ef4444;
        color: #b91c1c;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }

    .has-error {
        border-color: #ef4444 !important;
    }

    .error-msg {
        font-size: 0.75rem;
        color: #ef4444;
        margin-top: 0.25rem;
        font-weight: 600;
    }

    .tab-error {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ef4444;
    }

    /* Buttons */
    .btn-next {
        background-color: #0777be;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-next:hover {
        background-color: #0666a3;
    }

    .btn-back {
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-back:hover {
        background-color: #e5e7eb;
    }

    .btn-submit {
        background-color: #10b981;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    }

    .btn-submit:hover {
        background-color: #059669;
    }
</style>

<div x-data="questionForm({
    typeCode: '{{ $typeCode }}',
    options: {{ $jsonOptions }},
    correctAnswer: '{{ $correctAnswer }}',
    activeTab: '{{ $initialTab }}',
    skills: {{ $skills }},
    topics: {{ $topics }},
    selectedSkill: '{{ old('skill_id', $question->skill_id) }}',

    hasAttachment: {{ old('has_attachment', $question->has_attachment ?? 0) ? 'true' : 'false' }},
    attachmentType: '{{ old('attachment_type', $question->attachment_type ?? 'comprehension') }}',
    solutionHasVideo: {{ !empty($question->solution_video) ? 'true' : 'false' }}
})" class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden font-sans">

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" @submit="syncEditors">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="question_type_id" value="{{ $questionType->id ?? $question->question_type_id }}">

        {{-- Track Active Tab for Validation Returns --}}
        <input type="hidden" name="last_active_tab" x-model="activeTab">

        {{-- TABS HEADER --}}
        <div class="flex border-b border-gray-200 bg-white sticky top-0 z-20">
            <template x-for="tab in tabs">
                <button type="button" @click="activeTab = tab.id"
                    :class="{ 'text-[#0777be] border-b-[3px] border-[#0777be] bg-blue-50/30 font-bold': activeTab === tab
                        .id, 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': activeTab !== tab.id }"
                    class="flex-1 py-4 text-sm transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none relative">
                    <span x-text="tab.label" class="uppercase tracking-wider text-xs"></span>
                    <span
                        x-show="(tab.id === 'settings' && {{ $hasSettingsError ? 'true' : 'false' }}) || (tab.id === 'solution' && {{ $hasSolutionError ? 'true' : 'false' }}) || (tab.id === 'attachment' && {{ $hasAttachmentError ? 'true' : 'false' }})"
                        class="tab-error"></span>
                </button>
            </template>
        </div>

        <div class="p-6 md:p-8">

            {{-- ERRORS --}}
            @if (session('error'))
                <div class="error-box"><strong>Error:</strong> {{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="error-box"><strong>Validation Error!</strong> Please check fields marked red.</div>
            @endif

            {{-- 1. DETAILS TAB --}}
            <div x-show="activeTab === 'details'" class="space-y-8">
                <div class="space-y-2">
                    <label class="block text-gray-700 font-bold text-sm uppercase tracking-wide">Question Content <span
                            class="text-red-500">*</span></label>
                    <textarea name="question" id="editor_question" class="w-full opacity-0">{{ old('question', $question->question) }}</textarea>
                    @error('question')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DYNAMIC OPTIONS --}}
                <div class="bg-[#f8fafc] p-6 rounded-xl border border-gray-200">

                    {{-- MSA --}}
                    <template x-if="typeCode === 'MSA'">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Options (Select Correct Answer)</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div
                                    class="flex gap-4 mb-6 items-start bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="pt-3"><input type="radio" name="correct_answer"
                                            :value="index" :checked="correctAnswer == index"
                                            class="w-5 h-5 text-[#0777be]"></div>
                                    <div class="flex-1">
                                        <textarea :id="'editor_opt_' + index" :name="'options[' + index + '][option]'" class="w-full border-gray-300 rounded"></textarea>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="text-red-500 mt-2 p-2">X</button>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-[#0777be] text-[#0777be] font-bold rounded-lg hover:bg-blue-50">+
                                Add Option</button>
                        </div>
                    </template>

                    {{-- MMA --}}
                    <template x-if="typeCode === 'MMA' || typeCode === 'MMS'">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Options (Select Multiple)</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div
                                    class="flex gap-4 mb-6 items-start bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="pt-3"><input type="checkbox" :name="'options[' + index + '][is_correct]'"
                                            value="1" :checked="opt.is_correct" class="w-5 h-5 text-[#0777be]">
                                    </div>
                                    <div class="flex-1">
                                        <textarea :id="'editor_opt_' + index" :name="'options[' + index + '][option]'" class="w-full border-gray-300 rounded"></textarea>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="text-red-500 mt-2 p-2">X</button>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-[#0777be] text-[#0777be] font-bold rounded-lg hover:bg-blue-50">+
                                Add Option</button>
                        </div>
                    </template>

                    {{-- TOF --}}
                    <template x-if="typeCode === 'TOF'">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Select Correct Answer</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div
                                    class="flex items-center p-4 bg-white border border-gray-200 rounded-lg mb-3 shadow-sm">
                                    <input type="hidden" :name="'options[' + index + '][option]'" :value="opt.option">
                                    <input type="radio" name="correct_answer" :value="index"
                                        :checked="correctAnswer == index" class="w-5 h-5 text-[#0777be] mr-3">
                                    <span class="text-lg font-bold text-gray-700" x-text="opt.option"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- SAQ --}}
                    <template x-if="typeCode === 'SAQ'">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Acceptable Answers</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="mb-3 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex gap-2">
                                        <input type="text" :name="'options[' + index + '][option]'" x-model="opt.option"
                                            class="w-full border-gray-300 rounded-lg p-3" placeholder="Answer text">
                                        <button type="button" @click="removeOption(index)"
                                            class="px-4 bg-red-100 text-red-600 font-bold rounded-lg">X</button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-[#0777be] text-[#0777be] font-bold rounded-lg hover:bg-blue-50">+
                                Add Answer</button>
                        </div>
                    </template>

                    {{-- MTF --}}
                    <template x-if="typeCode === 'MTF'">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Match Pairs</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div
                                    class="grid grid-cols-2 gap-6 mb-6 p-4 border border-gray-200 rounded-xl bg-white shadow-sm relative">
                                    <div><label class="block text-xs font-bold text-gray-400 mb-2">Left</label>
                                        <textarea :id="'editor_opt_left_' + index" :name="'options[' + index + '][option]'"
                                            class="w-full h-24 border-gray-300 rounded"></textarea>
                                    </div>
                                    <div><label class="block text-xs font-bold text-gray-400 mb-2">Right</label>
                                        <textarea :id="'editor_opt_right_' + index" :name="'options[' + index + '][pair]'"
                                            class="w-full h-24 border-gray-300 rounded"></textarea>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 shadow-md">X</button>
                                </div>
                            </template>
                            <button type="button" @click="addPair()"
                                class="w-full py-3 border-2 border-dashed border-[#0777be] text-[#0777be] font-bold rounded-lg hover:bg-blue-50">+
                                Add Pair</button>
                        </div>
                    </template>

                    {{-- ORD --}}
                    <template x-if="typeCode === 'ORD'">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Correct Sequence</h3>
                            <template x-for="(opt, index) in options" :key="index">
                                <div
                                    class="flex gap-4 mb-6 items-start bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="pt-3 font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded"
                                        x-text="index+1"></div>
                                    <div class="flex-1">
                                        <textarea :id="'editor_opt_' + index" :name="'options[' + index + '][option]'" class="w-full border-gray-300 rounded"></textarea>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="text-red-500 mt-2 p-2">X</button>
                                </div>
                            </template>
                            <button type="button" @click="addOption()"
                                class="w-full py-3 border-2 border-dashed border-[#0777be] text-[#0777be] font-bold rounded-lg hover:bg-blue-50">+
                                Add Item</button>
                        </div>
                    </template>

                    {{-- FIB --}}
                    <template x-if="typeCode === 'FIB'">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <h4 class="font-bold text-blue-900">Instructions</h4>
                            <p class="text-sm text-blue-700 mt-2">Wrap words with double hashes <code>##</code>. E.g.
                                <strong>##Answer##</strong>.</p>
                        </div>
                    </template>
                </div>

                {{-- NEXT BUTTON 1 --}}
                <div class="flex justify-end pt-4 border-t mt-4">
                    <button type="button" @click="activeTab = 'settings'" class="btn-next">Next: Settings
                        &rarr;</button>
                </div>
            </div>

            {{-- 2. SETTINGS TAB --}}
            <div x-show="activeTab === 'settings'" class="space-y-6" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="form-label">Skill <span class="text-red-500">*</span></label>
                        <select name="skill_id" x-model="selectedSkill" @change="filterTopics()"
                            class="custom-select w-full"
                            :class="{ 'has-error': {{ $hasSettingsError ? 'true' : 'false' }} }">
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
                        <label class="form-label">Topic</label>
                        <select name="topic_id" class="custom-select w-full">
                            <option value="">-- Select Topic --</option>
                            <template x-for="topic in availableTopics" :key="topic.id">
                                <option :value="topic.id" x-text="topic.name"
                                    :selected="topic.id == '{{ $question->topic_id }}'"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Difficulty</label>
                        <div class="flex gap-3 mt-2">
                            @foreach ($difficultyLevels ?? [] as $level)
                                <label class="cursor-pointer">
                                    <input type="radio" name="difficulty_level_id" value="{{ $level->id }}"
                                        {{ old('difficulty_level_id', $question->difficulty_level_id) == $level->id ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span
                                        class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 bg-white peer-checked:bg-[#0777be] peer-checked:text-white peer-checked:border-[#0777be] peer-checked:shadow-md transition-all inline-block hover:border-[#0777be] hover:text-[#0777be]">{{ $level->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-[#0777be]/5 p-6 rounded-2xl border border-[#0777be]/10 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Marks (+ve) <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.25" name="default_marks"
                            value="{{ old('default_marks', $question->default_marks ?? 1) }}"
                            class="custom-input w-full"
                            :class="{ 'has-error': {{ $errors->has('default_marks') ? 'true' : 'false' }} }">
                        @error('default_marks')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Time (Seconds)</label>
                        <input type="number" name="default_time"
                            value="{{ old('default_time', $question->default_time ?? 60) }}"
                            class="custom-input w-full">
                    </div>
                </div>

                {{-- NAV BUTTONS 2 --}}
                <div class="flex justify-between pt-4 border-t mt-4">
                    <button type="button" @click="activeTab = 'details'" class="btn-back">&larr; Back</button>
                    <button type="button" @click="activeTab = 'solution'" class="btn-next">Next: Solution
                        &rarr;</button>
                </div>
            </div>

            {{-- 3. SOLUTION TAB --}}
            <div x-show="activeTab === 'solution'" class="space-y-8" style="display: none;">
                <div>
                    <label class="form-label mb-2">Detailed Solution</label>
                    <textarea name="solution" id="editor_solution" class="opacity-0">{{ old('solution', $question->solution) }}</textarea>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold text-gray-700">Video Solution</span>
                        <div class="flex items-center bg-white rounded border p-1">
                            <button type="button" @click="solutionHasVideo = true"
                                :class="{ 'bg-blue-600 text-white': solutionHasVideo, 'text-gray-500': !solutionHasVideo }"
                                class="px-3 py-1 text-xs rounded">Yes</button>
                            <button type="button" @click="solutionHasVideo = false"
                                :class="{ 'bg-gray-200 text-gray-700': !solutionHasVideo, 'text-gray-500': solutionHasVideo }"
                                class="px-3 py-1 text-xs rounded">No</button>
                        </div>
                    </div>
                    <div x-show="solutionHasVideo">
                        <input type="url" name="solution_video"
                            value="{{ old('solution_video', $question->solution_video) }}"
                            class="w-full border-gray-300 rounded-lg p-3" placeholder="Video URL (YouTube)"
                            x-model="videoUrl">
                    </div>
                </div>
                <div>
                    <label class="form-label mb-2 text-gray-500">Hint</label>
                    <textarea name="hint" id="editor_hint" class="opacity-0">{{ old('hint', $question->hint) }}</textarea>
                </div>

                {{-- NAV BUTTONS 3 --}}
                <div class="flex justify-between pt-4 border-t mt-4">
                    <button type="button" @click="activeTab = 'settings'" class="btn-back">&larr; Back</button>
                    <button type="button" @click="activeTab = 'attachment'" class="btn-next">Next: Attachment
                        &rarr;</button>
                </div>
            </div>

            {{-- 4. ATTACHMENT TAB (FINAL STEP) --}}
            <div x-show="activeTab === 'attachment'" class="space-y-8" style="display: none;">
                <div
                    class="bg-[#f062a4]/5 p-6 rounded-2xl border border-[#f062a4]/20 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Question Attachment</h3>
                        <p class="text-sm text-gray-500">Add comprehension, audio, or video.</p>
                    </div>
                    <div class="flex items-center bg-white rounded-lg border border-[#f062a4]/20 p-1">
                        <button type="button" @click="hasAttachment = false"
                            :class="{ 'bg-[#f062a4] text-white': !hasAttachment, 'text-gray-500': hasAttachment }"
                            class="px-4 py-1.5 rounded-md text-sm font-bold">None</button>
                        <button type="button" @click="hasAttachment = true"
                            :class="{ 'bg-[#f062a4] text-white': hasAttachment, 'text-gray-500': !hasAttachment }"
                            class="px-4 py-1.5 rounded-md text-sm font-bold">Add</button>
                    </div>
                    <input type="hidden" name="has_attachment" :value="hasAttachment ? 1 : 0">
                </div>

                <div x-show="hasAttachment" class="space-y-6">
                    <div>
                        <label class="form-label mb-3 font-bold block">Type</label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach (['comprehension' => 'Comprehension', 'audio' => 'Audio File', 'video' => 'Video File'] as $key => $label)
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="attachment_type" value="{{ $key }}"
                                        x-model="attachmentType" class="peer sr-only">
                                    <div
                                        class="p-4 rounded-xl border border-gray-200 bg-white hover:border-[#f062a4] peer-checked:border-[#f062a4] peer-checked:bg-pink-50 transition-all text-center group">
                                        <div
                                            class="text-sm font-bold text-gray-600 group-hover:text-[#f062a4] peer-checked:text-[#f062a4]">
                                            {{ $label }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="attachmentType === 'comprehension'"
                        class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <label class="form-label mb-2 block font-bold">Select Passage</label>
                        <select name="comprehension_id" class="w-full border-gray-300 rounded-lg p-3">
                            <option value="">-- Choose a Passage --</option>
                            @foreach ($passages ?? [] as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('comprehension_passage_id', $question->comprehension_passage_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="attachmentType === 'audio' || attachmentType === 'video'"
                        class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <label class="form-label mb-2 block font-bold">Media Link / ID</label>
                        <input type="text" name="attachment_options[link]"
                            value="{{ old('attachment_options.link', data_get($question->attachment_options, 'link')) }}"
                            class="w-full border-gray-300 rounded-lg p-3" placeholder="Enter URL here">
                    </div>
                </div>

                {{-- FINAL SUBMIT BUTTON --}}
                <div class="flex justify-between pt-4 border-t mt-4">
                    <button type="button" @click="activeTab = 'solution'" class="btn-back">&larr; Back</button>
                    <button type="submit"
                        class="btn-submit">{{ $isEdit ? 'Update Question' : 'Submit Question' }}</button>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- SCRIPTS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.1.0/tinymce.min.js"></script>
<script>
    const getTinyConfig = (h = 150) => ({
        height: h,
        menubar: false,
        plugins: 'lists link image charmap preview',
        toolbar: 'bold italic underline | bullist numlist | link image | removeformat',
        content_style: 'body { font-family:Inter,sans-serif; font-size:14px; padding: 10px 15px; }',
        convert_urls: false,
        setup: function(editor) {
            editor.on('change keyup', function() {
                editor.save();
            });
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('questionForm', (config) => ({
            typeCode: config.typeCode,
            options: config.options,
            correctAnswer: config.correctAnswer,
            activeTab: config.activeTab,
            skills: config.skills,
            allTopics: config.topics,
            availableTopics: [],
            selectedSkill: config.selectedSkill,

            hasAttachment: config.hasAttachment,
            attachmentType: config.attachmentType,
            solutionHasVideo: config.solutionHasVideo,
            videoUrl: '',

            formErrors: config.formErrors,
            tabErrors: config.tabErrors,

            tabs: [{
                    id: 'details',
                    label: 'Details'
                },
                {
                    id: 'settings',
                    label: 'Settings'
                },
                {
                    id: 'solution',
                    label: 'Solution'
                },
                {
                    id: 'attachment',
                    label: 'Attachment'
                }
            ],

            init() {
                this.filterTopics();
                this.$nextTick(() => {
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
                        ...getTinyConfig(150)
                    });
                    this.initOptionEditors();
                });
            },

            syncEditors() {
                tinymce.triggerSave();
            },

            filterTopics() {
                if (!this.selectedSkill) {
                    this.availableTopics = [];
                    return;
                }
                this.availableTopics = this.allTopics.filter(t => t.skill_id == this.selectedSkill);
            },

            initOptionEditors() {
                if (this.typeCode === 'FIB' || this.typeCode === 'SAQ') return;

                this.options.forEach((opt, index) => {
                    if (this.typeCode === 'MTF') {
                        if (!tinymce.get('editor_opt_left_' + index)) {
                            tinymce.init({
                                selector: '#editor_opt_left_' + index,
                                ...getTinyConfig(150),
                                setup: (e) => e.on('change keyup', () => {
                                    this.options[index].option = e
                                        .getContent()
                                })
                            });
                            if (opt.option) tinymce.get('editor_opt_left_' + index)
                                .setContent(opt.option);
                        }
                        if (!tinymce.get('editor_opt_right_' + index)) {
                            tinymce.init({
                                selector: '#editor_opt_right_' + index,
                                ...getTinyConfig(150),
                                setup: (e) => e.on('change keyup', () => {
                                    this.options[index].pair = e
                                    .getContent()
                                })
                            });
                            if (opt.pair) tinymce.get('editor_opt_right_' + index)
                                .setContent(opt.pair);
                        }
                    } else {
                        if (!tinymce.get('editor_opt_' + index)) {
                            tinymce.init({
                                selector: '#editor_opt_' + index,
                                ...getTinyConfig(200),
                                setup: (editor) => {
                                    editor.on('init', () => {
                                        if (opt.option) editor
                                            .setContent(opt.option);
                                    });
                                    editor.on('change keyup', () => this
                                        .options[index].option = editor
                                        .getContent());
                                }
                            });
                        }
                    }
                });
            },

            addOption() {
                this.options.push({
                    option: '',
                    image: null,
                    is_correct: false
                });
                this.$nextTick(() => this.initOptionEditors());
            },

            addPair() {
                this.options.push({
                    option: '',
                    pair: ''
                });
                this.$nextTick(() => this.initOptionEditors());
            },

            removeOption(index) {
                if (tinymce.get('editor_opt_' + index)) tinymce.get('editor_opt_' + index).remove();
                if (tinymce.get('editor_opt_left_' + index)) tinymce.get('editor_opt_left_' + index)
                    .remove();
                if (tinymce.get('editor_opt_right_' + index)) tinymce.get('editor_opt_right_' +
                    index).remove();
                this.options.splice(index, 1);
            },

            hasError(field) {
                return this.formErrors[field] !== undefined;
            },
            getError(field) {
                return this.formErrors[field] ? this.formErrors[field][0] : '';
            }
        }));
    });
</script>
