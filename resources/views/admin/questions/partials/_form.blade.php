@php
    $isEdit = $question->exists;
    $routePrefix = request()->routeIs('instructor.*') ? 'instructor.' : 'admin.';
    $action = $isEdit ? route($routePrefix . 'questions.update', $question->id) : route($routePrefix . 'questions.store');

    // Decode Options or Set Default
    $currentOptions = $question->options ?? [];
    if(empty($currentOptions) || is_string($currentOptions)) {
        $currentOptions = is_string($currentOptions) ? json_decode($currentOptions, true) : [
            ['option' => '', 'image' => null, 'is_correct' => false],
            ['option' => '', 'image' => null, 'is_correct' => false],
            ['option' => '', 'image' => null, 'is_correct' => false],
            ['option' => '', 'image' => null, 'is_correct' => false]
        ];
    }
    $jsonOptions = json_encode($currentOptions);
    $correctAnswer = old('correct_answer', $question->correct_answer);
@endphp

{{-- Main Container with AlpineJS --}}
<div x-data="questionForm({
        options: {{ $jsonOptions }},
        activeTab: 'details',
        hasAttachment: {{ old('has_attachment', $question->has_attachment ?? 0) ? 'true' : 'false' }},
        attachmentType: '{{ old('attachment_type', $question->attachment_type ?? 'comprehension') }}',
        solutionHasVideo: {{ !empty($question->solution_video) ? 'true' : 'false' }},
        correctAnswer: '{{ $correctAnswer }}'
    })"
    @fm-selected.window="handleFmSelection($event.detail)"
    class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden font-sans">

    <form action="{{ $action }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="question_type_id" value="{{ $questionType->id ?? $question->question_type_id }}">

        {{-- 1. PREMIUM TABS --}}
        <div class="flex border-b border-gray-200 bg-white sticky top-0 z-20">
            <template x-for="tab in tabs">
                <button type="button"
                    @click="activeTab = tab.id"
                    :class="{
                        'text-[#0777be] border-b-[3px] border-[#0777be] bg-blue-50/30 font-bold': activeTab === tab.id,
                        'text-gray-500 hover:text-gray-700 hover:bg-gray-50': activeTab !== tab.id
                    }"
                    class="flex-1 py-4 text-sm transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <span x-text="tab.icon" class="text-lg"></span>
                    <span x-text="tab.label" class="uppercase tracking-wider text-xs"></span>
                </button>
            </template>
        </div>

        <div class="p-6 md:p-8">

            {{-- TAB 1: DETAILS & OPTIONS --}}
            <div x-show="activeTab === 'details'" class="space-y-8" style="display: none;">

                {{-- Question Editor --}}
                <div class="space-y-2">
                    <label class="block text-gray-700 font-bold text-xs uppercase tracking-wide flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#0777be] rounded-full"></span>
                        Question Content <span class="text-red-500">*</span>
                    </label>
                    <div class="rounded-xl overflow-hidden border border-gray-300 shadow-sm focus-within:ring-2 focus-within:ring-[#0777be]/20 transition-all">
                        <textarea name="question" id="editor_question" class="w-full h-32 opacity-0">{{ old('question', $question->question) }}</textarea>
                    </div>
                    @error('question') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- OPTIONS SECTION --}}
                <div class="space-y-5">
                    <div class="flex justify-between items-end border-b border-gray-100 pb-3">
                        <label class="block text-gray-700 font-bold text-xs uppercase tracking-wide flex items-center gap-2">
                            <span class="w-1 h-4 bg-[#f062a4] rounded-full"></span>
                            Answer Options
                        </label>
                        <button type="button" @click="renderAllMath()" class="text-xs flex items-center gap-1 text-gray-500 hover:text-[#0777be] transition-colors font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Refresh Math
                        </button>
                    </div>

                    <div class="grid gap-5">
                        <template x-for="(opt, index) in options" :key="index">
                            <div class="group relative flex gap-4 p-5 border border-gray-200 rounded-xl hover:border-[#0777be]/30 hover:shadow-lg hover:shadow-blue-50/50 transition-all bg-white">

                                {{-- Correct Answer Selector --}}
                                <div class="pt-8 w-14 shrink-0 flex flex-col items-center border-r border-gray-100 pr-4 mr-2">
                                    <label class="cursor-pointer group/radio relative" title="Mark as Correct">
                                        <input type="radio" name="correct_answer" :value="opt.option"
                                            :checked="opt.is_correct || opt.option == correctAnswer"
                                            @change="correctAnswer = opt.option"
                                            class="peer sr-only">

                                        {{-- Custom Radio UI --}}
                                        <div class="w-8 h-8 rounded-full border-2 border-gray-300 peer-checked:border-[#94c940] peer-checked:bg-[#94c940] transition-all flex items-center justify-center text-white shadow-sm">
                                            <svg class="w-5 h-5 opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </label>
                                </div>

                                {{-- Input Area --}}
                                <div class="flex-1 space-y-3">
                                    {{-- Toolbar --}}
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase bg-gray-100 px-2 py-1 rounded tracking-wider" x-text="'Option ' + (index + 1)"></span>

                                        <div class="flex items-center gap-2">
                                            {{-- Math Button --}}
                                            <button type="button" @click="openMathModal(index)" class="flex items-center gap-1 text-xs px-3 py-1.5 bg-white text-gray-600 border border-gray-200 rounded-md hover:border-[#0777be] hover:text-[#0777be] transition font-medium shadow-sm">
                                                <span class="font-serif italic font-bold text-sm leading-none">∑</span> Math
                                            </button>

                                            {{-- File Manager Button --}}
                                            <button type="button" @click="openFileManager(index)" class="flex items-center gap-1 text-xs px-3 py-1.5 bg-white text-gray-600 border border-gray-200 rounded-md hover:border-[#f062a4] hover:text-[#f062a4] transition font-medium shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Image
                                            </button>

                                            {{-- Delete --}}
                                            <button type="button" @click="removeOption(index)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-md transition ml-1" title="Remove Option">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex gap-4 items-start">
                                        <div class="flex-1 space-y-2">
                                            <textarea :name="'options['+index+'][option]'" x-model="opt.option"
                                                @input.debounce.500ms="renderMathPreview(index)"
                                                rows="2"
                                                class="w-full border-gray-300 bg-white rounded-lg focus:border-[#0777be] focus:ring-[#0777be] text-sm shadow-sm p-3 resize-y transition-all"
                                                placeholder="Type option text here..."></textarea>

                                            {{-- Hidden input for Image --}}
                                            <input type="hidden" :name="'options['+index+'][image]'" x-model="opt.image">

                                            {{-- Live Math Preview --}}
                                            <div :id="'math-preview-' + index"
                                                 class="min-h-[24px] text-sm text-gray-800 bg-gray-50/50 border border-gray-100 rounded px-3 py-2"
                                                 x-show="opt.option && opt.option.includes('\\(')">
                                            </div>
                                        </div>

                                        {{-- Image Preview from File Manager --}}
                                        <div x-show="opt.image" class="relative group/img shrink-0">
                                            <div class="h-24 w-24 rounded-lg border border-gray-200 bg-gray-50 p-1 shadow-sm overflow-hidden flex items-center justify-center">
                                                <img :src="opt.image" class="max-h-full max-w-full object-contain">
                                            </div>
                                            <button type="button" @click="removeImage(index)" class="absolute -top-2 -right-2 bg-white text-red-500 border border-red-100 rounded-full p-1 shadow-md hover:bg-red-50 transition opacity-0 group-hover/img:opacity-100">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addOption()" class="w-full py-4 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-[#0777be] hover:text-[#0777be] hover:bg-blue-50/30 transition font-bold flex justify-center items-center gap-2 group">
                            <div class="w-8 h-8 rounded-full bg-gray-200 group-hover:bg-[#0777be] group-hover:text-white flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            Add New Option
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB 2: SETTINGS --}}
            <div x-show="activeTab === 'settings'" class="grid grid-cols-1 md:grid-cols-2 gap-8" style="display: none;">
                <div class="space-y-6">
                    {{-- Custom Select: Skill --}}
                    <div>
                        <label class="form-label">Skill / Subject</label>
                        <div class="relative">
                            <select name="skill_id" class="custom-select w-full">
                                <option value="">Select Skill</option>
                                @foreach($skills as $skill)
                                    <option value="{{ $skill->id }}" {{ old('skill_id', $question->skill_id) == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Select: Topic --}}
                    <div>
                        <label class="form-label">Topic</label>
                        <div class="relative">
                            <select name="topic_id" class="custom-select w-full">
                                <option value="">Select Topic</option>
                                @foreach($topics ?? [] as $topic)
                                    <option value="{{ $topic->id }}" {{ old('topic_id', $question->topic_id) == $topic->id ? 'selected' : '' }}>{{ $topic->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Difficulty --}}
                    <div>
                        <label class="form-label">Difficulty</label>
                        <div class="flex gap-3 mt-2">
                            @foreach($difficultyLevels ?? [] as $level)
                                <label class="cursor-pointer">
                                    <input type="radio" name="difficulty_level_id" value="{{ $level->id }}" {{ old('difficulty_level_id', $question->difficulty_level_id) == $level->id ? 'checked' : '' }} class="peer sr-only">
                                    <span class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 bg-white peer-checked:bg-[#0777be] peer-checked:text-white peer-checked:border-[#0777be] peer-checked:shadow-md transition-all inline-block hover:border-[#0777be] hover:text-[#0777be]">
                                        {{ $level->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-6">
                    <div class="bg-[#0777be]/5 p-6 rounded-2xl border border-[#0777be]/10 space-y-5">
                        <h4 class="text-xs font-extrabold text-[#0777be] uppercase tracking-wider mb-2">Scoring & Timing</h4>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Marks (+ve)</label>
                            <input type="number" step="0.25" name="default_marks" value="{{ old('default_marks', $question->default_marks ?? 1) }}"
                                   class="custom-input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Time (Seconds)</label>
                            <input type="number" name="default_time" value="{{ old('default_time', $question->default_time ?? 60) }}"
                                   class="custom-input w-full">
                        </div>
                    </div>

                    @if(Auth::user()->hasRole('admin'))
                    <div class="pt-2">
                        <label class="flex items-center cursor-pointer p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition bg-white shadow-sm">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $question->is_active) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#94c940]"></div>
                            </div>
                            <div class="ml-4">
                                <span class="block text-gray-900 font-bold text-sm">Active Status</span>
                                <span class="block text-gray-500 text-xs mt-0.5">Visible to students immediately</span>
                            </div>
                        </label>
                    </div>
                    @endif
                </div>
            </div>

            {{-- TAB 3: SOLUTION --}}
            <div x-show="activeTab === 'solution'" class="space-y-8" style="display: none;">
                <div>
                    <label class="form-label mb-2">Detailed Solution</label>
                    <textarea name="solution" id="editor_solution" class="opacity-0">{{ old('solution', $question->solution) }}</textarea>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-white p-6 rounded-2xl border border-blue-100">
                    <div class="flex items-center justify-between mb-4">
                        <label class="flex items-center gap-2 font-bold text-gray-800">
                            <svg class="w-5 h-5 text-[#0777be]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.414.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            Enable Solution Video
                        </label>
                        <div class="flex items-center bg-white rounded-lg border border-gray-200 p-1 shadow-sm">
                            <button type="button" @click="solutionHasVideo = true" :class="{'bg-[#0777be] text-white shadow-sm': solutionHasVideo, 'text-gray-500 hover:text-gray-700': !solutionHasVideo}" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">Yes</button>
                            <button type="button" @click="solutionHasVideo = false" :class="{'bg-gray-200 text-gray-700 shadow-inner': !solutionHasVideo, 'text-gray-500 hover:text-gray-700': solutionHasVideo}" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">No</button>
                        </div>
                    </div>

                    <div x-show="solutionHasVideo" x-transition class="mt-4 p-5 bg-white rounded-xl border border-blue-100 shadow-sm">
                        <label class="text-xs font-bold text-gray-400 uppercase mb-2 block">Video Link (YouTube/Vimeo)</label>
                        <div class="flex gap-2">
                            <input type="url" name="solution_video" value="{{ old('solution_video', $question->solution_video) }}"
                                   class="custom-input w-full"
                                   placeholder="https://youtube.com/watch?v=..."
                                   x-model="videoUrl">
                        </div>
                        <div x-show="videoUrl" class="mt-3 text-xs text-[#94c940] flex items-center gap-1 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Video link ready
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label mb-2 text-gray-500">Hint (Optional)</label>
                    <textarea name="hint" id="editor_hint" class="opacity-0">{{ old('hint', $question->hint) }}</textarea>
                </div>
            </div>

            {{-- TAB 4: ATTACHMENT --}}
            <div x-show="activeTab === 'attachment'" class="space-y-8" style="display: none;">
                <div class="flex items-center justify-between p-6 bg-[#f062a4]/5 rounded-2xl border border-[#f062a4]/20">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#f062a4]/10 flex items-center justify-center text-[#f062a4]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Question Attachment</h3>
                            <p class="text-sm text-gray-500">Add a passage, audio, or video for this question.</p>
                        </div>
                    </div>
                    <div class="flex items-center bg-white rounded-lg border border-[#f062a4]/20 p-1 shadow-sm">
                        <button type="button" @click="hasAttachment = false" :class="{'bg-[#f062a4] text-white shadow-sm': !hasAttachment, 'text-gray-500 hover:text-gray-700': hasAttachment}" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">None</button>
                        <button type="button" @click="hasAttachment = true" :class="{'bg-[#f062a4] text-white shadow-sm': hasAttachment, 'text-gray-500 hover:text-gray-700': !hasAttachment}" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">Add</button>
                    </div>
                </div>

                <div x-show="hasAttachment" x-transition class="space-y-6">
                    <div>
                        <label class="form-label mb-3">Attachment Type</label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach(['comprehension' => 'Comprehension', 'audio' => 'Audio File', 'video' => 'Video File'] as $key => $label)
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="attachment_type" value="{{ $key }}" x-model="attachmentType" class="peer sr-only">
                                    <div class="p-4 rounded-xl border border-gray-200 bg-white hover:border-[#f062a4] peer-checked:border-[#f062a4] peer-checked:bg-pink-50 transition-all text-center group">
                                        <div class="text-sm font-bold text-gray-600 group-hover:text-[#f062a4] peer-checked:text-[#f062a4]">{{ $label }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Comprehension --}}
                    <div x-show="attachmentType === 'comprehension'" class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <label class="form-label mb-2">Select Passage</label>
                        <div class="relative">
                            <select name="comprehension_id" class="custom-select w-full">
                                <option value="">-- Choose a Passage --</option>
                                @foreach($passages ?? [] as $p)
                                    <option value="{{ $p->id }}" {{ old('comprehension_passage_id', $question->comprehension_passage_id) == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Audio/Video --}}
                    <div x-show="attachmentType === 'audio' || attachmentType === 'video'" class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <label class="form-label mb-2">Media Link / ID</label>
                        <input type="text" name="attachment_options[link]"
                               value="{{ old('attachment_options.link', $question->attachment_options['link'] ?? '') }}"
                               class="custom-input w-full" placeholder="Enter URL here">
                    </div>
                </div>
            </div>

        </div>

        {{-- FORM FOOTER --}}
        <div class="bg-gray-50 px-8 py-5 border-t border-gray-200 flex justify-between items-center rounded-b-2xl">
            <a href="{{ route($routePrefix . 'questions.index') }}" class="text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-[#94c940] text-white rounded-xl shadow-lg shadow-green-200 hover:bg-green-600 font-bold tracking-wide transform hover:-translate-y-0.5 transition-all">
                {{ $isEdit ? 'UPDATE QUESTION' : 'SAVE QUESTION' }}
            </button>
        </div>
    </form>

    {{-- MATH MODAL --}}
    <div x-show="showMathModal" style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
         x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-100" @click.outside="closeMathModal()">
            <div class="bg-[#0777be] px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg tracking-wide">Insert Math Formula</h3>
                <button @click="closeMathModal()" class="text-white/80 hover:text-white transition text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">LaTeX Expression</label>
                    <textarea x-model="mathInput" @input="updateMathPreview"
                        class="w-full h-28 border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0777be]/20 focus:border-[#0777be] font-mono text-sm p-3 bg-gray-50"
                        placeholder="e.g. \frac{-b \pm \sqrt{b^2-4ac}}{2a}"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Live Preview</label>
                    <div class="bg-white p-6 rounded-xl min-h-[80px] flex items-center justify-center border border-gray-200 shadow-inner">
                        <div id="math-preview-target" class="text-2xl text-gray-800"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" @click="closeMathModal()" class="px-5 py-2 text-gray-600 font-medium hover:bg-gray-100 rounded-lg transition">Cancel</button>
                <button type="button" @click="insertMath()" class="px-6 py-2 bg-[#0777be] text-white rounded-lg hover:bg-[#0666a3] font-bold shadow-md transition">Insert</button>
            </div>
        </div>
    </div>

</div>

{{-- CSS --}}
<style>
    .form-label { display: block; font-weight: 700; color: #374151; font-size: 0.875rem; }
    /* Premium Select Box */
    .custom-select { appearance: none; background-color: #fff; border: 1px solid #d1d5db; color: #374151; padding: 0.75rem 1rem; border-radius: 0.75rem; width: 100%; font-size: 0.875rem; line-height: 1.25; transition: all 0.2s; }
    .custom-select:focus { outline: none; border-color: #0777be; ring: 2px solid rgba(7, 119, 190, 0.2); }
    /* Premium Input */
    .custom-input { width: 100%; border: 1px solid #d1d5db; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem; transition: all 0.2s; }
    .custom-input:focus { outline: none; border-color: #0777be; box-shadow: 0 0 0 3px rgba(7, 119, 190, 0.1); }
    /* TinyMCE Polish */
    .tox-tinymce { border-radius: 0.75rem !important; border-color: #e5e7eb !important; overflow: hidden; }
</style>

{{-- SCRIPTS --}}
<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.1.0/tinymce.min.js" referrerpolicy="origin"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('questionForm', (config) => ({
        tabs: [
            { id: 'details', label: 'Details', icon: '📝' },
            { id: 'settings', label: 'Settings', icon: '⚙️' },
            { id: 'solution', label: 'Solution', icon: '💡' },
            { id: 'attachment', label: 'Media', icon: '📎' }
        ],
        activeTab: config.activeTab,
        options: config.options,
        correctAnswer: config.correctAnswer,
        hasAttachment: config.hasAttachment,
        attachmentType: config.attachmentType,
        solutionHasVideo: config.solutionHasVideo,
        showMathModal: false,
        mathInput: '',
        videoUrl: '',
        activeOptionIndex: null, // Tracks which option asked for image

        init() {
            this.$nextTick(() => { this.renderAllMath(); });
        },

        // --- File Manager Integration ---
        openFileManager(index) {
            this.activeOptionIndex = index;
            // Open FM Popup
            window.open('/admin/file-manager/popup', 'fm', 'width=1000,height=600');
        },

        handleFmSelection(url) {
            if (this.activeOptionIndex !== null && this.options[this.activeOptionIndex]) {
                this.options[this.activeOptionIndex].image = url;
                this.activeOptionIndex = null; // Reset
            }
        },

        addOption() {
            if(this.options.length < 6) {
                this.options.push({ option: '', image: null, is_correct: false });
            }
        },
        removeOption(index) {
            if(this.options.length > 2) this.options.splice(index, 1);
            else Swal.fire('Warning', 'Minimum 2 options required.', 'warning');
        },
        removeImage(index) {
            this.options[index].image = null;
        },

        // Math Logic
        openMathModal(index) {
            this.activeOptionIndex = index;
            this.mathInput = '';
            document.getElementById('math-preview-target').innerHTML = 'Preview...';
            this.showMathModal = true;
        },
        closeMathModal() {
            this.showMathModal = false;
        },
        updateMathPreview() {
            const preview = document.getElementById('math-preview-target');
            preview.innerHTML = '\\(' + this.mathInput + '\\)';
            if(window.MathJax) MathJax.typesetPromise([preview]);
        },
        insertMath() {
            if (this.activeOptionIndex !== null) {
                const formula = ' \\(' + this.mathInput + '\\) ';
                this.options[this.activeOptionIndex].option += formula;
                this.$nextTick(() => { this.renderMathPreview(this.activeOptionIndex); });
                this.closeMathModal();
            }
        },
        renderMathPreview(index) {
            const previewId = 'math-preview-' + index;
            const el = document.getElementById(previewId);
            if(el && this.options[index].option) {
                el.innerHTML = this.options[index].option;
                if(window.MathJax) MathJax.typesetPromise([el]);
            }
        },
        renderAllMath() {
            this.options.forEach((opt, index) => { this.renderMathPreview(index); });
        }
    }));
});

// Bridge function for File Manager Popup
// This function gets called by the FM popup window
window.fmSetLink = function(url) {
    // Dispatch event to Alpine
    window.dispatchEvent(new CustomEvent('fm-selected', { detail: url }));
};

// TinyMCE Init
window.onload = function() {
    const commonConfig = {
        height: 250,
        menubar: false,
        plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen table help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat | help',
        content_style: 'body { font-family:Inter,sans-serif; font-size:14px }'
    };
    tinymce.init({ selector: '#editor_question', ...commonConfig, height: 300 });
    tinymce.init({ selector: '#editor_solution', ...commonConfig });
    tinymce.init({ selector: '#editor_hint', ...commonConfig, height: 150 });
};
</script>
