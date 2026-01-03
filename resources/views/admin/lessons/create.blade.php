@extends('layouts.admin')
@section('title', 'Create Lesson')

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4" x-data="lessonWizard()">

        {{-- Header --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create New Lesson</h1>
                <p class="text-sm text-gray-500">Step <span x-text="step"></span> of 3</p>
            </div>
            <div>
                <a href="{{ route('admin.lessons.index') }}"
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    &larr; Back to List
                </a>
            </div>
        </div>

        {{-- Stepper Navigation --}}
        <div class="mb-8 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                {{-- Step 1 Indicator --}}
                <div class="flex items-center gap-2 cursor-pointer" @click="step = 1">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full font-bold text-sm transition-all"
                        :class="step >= 1 ? 'bg-[#0777be] text-white' : 'bg-gray-200 text-gray-500'">1</div>
                    <span class="font-semibold hidden sm:block"
                        :class="step >= 1 ? 'text-[#0777be]' : 'text-gray-500'">Basic Info</span>
                </div>

                <div class="flex-1 h-1 mx-4 bg-gray-200 rounded">
                    <div class="h-full bg-[#0777be] rounded transition-all duration-300"
                        :style="'width: ' + ((step - 1) * 50) + '%'"></div>
                </div>

                {{-- Step 2 Indicator --}}
                <div class="flex items-center gap-2 cursor-pointer" @click="step = 2">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full font-bold text-sm transition-all"
                        :class="step >= 2 ? 'bg-[#0777be] text-white' : 'bg-gray-200 text-gray-500'">2</div>
                    <span class="font-semibold hidden sm:block"
                        :class="step >= 2 ? 'text-[#0777be]' : 'text-gray-500'">Content</span>
                </div>

                <div class="flex-1 h-1 mx-4 bg-gray-200 rounded">
                    <div class="h-full bg-[#0777be] rounded transition-all duration-300"
                        :style="'width: ' + (step >= 3 ? '100%' : '0%')"></div>
                </div>

                {{-- Step 3 Indicator --}}
                <div class="flex items-center gap-2 cursor-pointer" @click="step = 3">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full font-bold text-sm transition-all"
                        :class="step >= 3 ? 'bg-[#0777be] text-white' : 'bg-gray-200 text-gray-500'">3</div>
                    <span class="font-semibold hidden sm:block"
                        :class="step >= 3 ? 'text-[#0777be]' : 'text-gray-500'">Settings</span>
                </div>
            </div>
        </div>

        {{-- Form Content --}}
        <form action="{{ route('admin.lessons.store') }}" method="POST" id="lessonForm">
            @csrf
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-8 min-h-[400px]">

                    {{-- STEP 1: BASIC INFO --}}
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-6">Basic Information</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Lesson Title <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title" x-model="form.title"
                                    class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-[#0777be] focus:border-[#0777be]"
                                    placeholder="Enter lesson title" required>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Skill <span
                                            class="text-red-500">*</span></label>
                                    <select name="skill_id" class="w-full border-gray-300 rounded-lg p-2.5" required>
                                        <option value="">Select Skill</option>
                                        @foreach ($skills as $skill)
                                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Topic</label>
                                    <select name="topic_id" class="w-full border-gray-300 rounded-lg p-2.5">
                                        <option value="">Select Topic</option>
                                        @foreach ($topics as $topic)
                                            <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Difficulty <span
                                            class="text-red-500">*</span></label>
                                    <select name="difficulty_level_id" class="w-full border-gray-300 rounded-lg p-2.5"
                                        required>
                                        @foreach ($difficultyLevels as $level)
                                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Read Time (Mins) <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" name="duration" class="w-full border-gray-300 rounded-lg p-2.5"
                                        min="1" value="5" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 2: CONTENT (CKEDITOR) --}}
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-6">Lesson Content</h3>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Body Content <span
                                    class="text-red-500">*</span></label>
                            <textarea name="body" id="editor_body" class="w-full border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>

                    {{-- STEP 3: SETTINGS --}}
                    <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-6">Final Settings</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-gray-800">Paid Content</h4>
                                    <p class="text-xs text-gray-500">Accessible only to subscribed users.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_paid" value="1" class="sr-only peer" checked>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]">
                                    </div>
                                </label>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-gray-800">Status (Active)</h4>
                                    <p class="text-xs text-gray-500">Visible to students immediately.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div
                            class="mt-8 p-4 bg-blue-50 text-blue-800 rounded-lg text-sm border border-blue-100 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Review your details before clicking 'Create Lesson'.
                        </div>
                    </div>

                </div>

                {{-- Footer Buttons --}}
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center">
                    <button type="button" x-show="step > 1" @click="step--"
                        class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-all shadow-sm">
                        &larr; Previous
                    </button>
                    <div x-show="step === 1"></div> {{-- Spacer --}}

                    <button type="button" x-show="step < 3" @click="validateAndNext()"
                        class="px-6 py-2.5 bg-[#0777be] text-white font-medium rounded-lg hover:bg-[#0666a3] transition-all shadow-md">
                        Next Step &rarr;
                    </button>

                    <button type="submit" x-show="step === 3"
                        class="px-8 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-all shadow-md flex items-center gap-2">
                        <i class="fas fa-check"></i> Create Lesson
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('editor_body');

        function lessonWizard() {
            return {
                step: 1,
                form: {
                    title: ''
                },
                validateAndNext() {
                    if (this.step === 1) {
                        if (this.form.title.trim() === '') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Please enter a lesson title.'
                            });
                            return;
                        }
                        // Add more validation for dropdowns if needed
                    }
                    this.step++;
                }
            }
        }
    </script>
@endsection
