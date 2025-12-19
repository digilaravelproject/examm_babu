@extends('layouts.admin')

@section('title', 'Edit Question')
@section('header', 'Edit Question')

@php
    $routePrefix = request()->routeIs('instructor.*') ? 'instructor.' : 'admin.';
    $tabs = ['details' => 'Details', 'settings' => 'Settings', 'solution' => 'Solution', 'attachment' => 'Attachment'];
@endphp

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">

    {{-- Header & Status --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
                Edit Question
                @if($question->is_active)
                    <span class="px-2 py-0.5 text-xs bg-[#94c940] text-white rounded-full">Live</span>
                @else
                    <span class="px-2 py-0.5 text-xs bg-orange-500 text-white rounded-full">Pending Approval</span>
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-500">Code: <span class="font-mono text-[#f062a4]">{{ $question->code }}</span> | Type: {{ $question->questionType->name }}</p>
        </div>
        <div class="flex gap-2">
            @if(Auth::user()->hasRole('admin') && !$question->is_active)
                <form action="{{ route('admin.questions.approve', $question->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="px-4 py-2 bg-[#94c940] text-white rounded-lg hover:bg-green-600 shadow-sm transition">
                        ✓ Approve Question
                    </button>
                </form>
            @endif
            <a href="{{ route($routePrefix . 'questions.index') }}" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Back
            </a>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px space-x-8" aria-label="Tabs">
            @foreach($tabs as $key => $label)
                <a href="{{ route($routePrefix . 'questions.edit', ['question' => $question->id, 'tab' => $key]) }}"
                   class="{{ $activeTab == $key ? 'border-[#0777be] text-[#0777be]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                          whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- TAB CONTENT --}}
    <div class="p-6 bg-white border border-t-0 border-gray-200 shadow-sm rounded-b-xl">

        {{-- 1. DETAILS TAB --}}
        @if($activeTab == 'details')
            <form action="{{ route($routePrefix . 'questions.update', $question->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Question Text</label>
                        <textarea name="question" id="details_editor" rows="5" class="w-full border-gray-300 rounded-lg">{{ old('question', $question->question) }}</textarea>
                    </div>

                    {{-- Options Logic (Simplified for Demo) --}}
                    {{-- Need JS logic here to handle Correct Answer selection --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Options & Correct Answer</label>
                        <div class="p-4 border border-gray-100 rounded-lg bg-gray-50">
                            @php $options = is_array($question->options) ? $question->options : json_decode($question->options, true) ?? []; @endphp

                            @foreach($options as $index => $opt)
                                <div class="flex items-center gap-3 mb-3">
                                    <input type="radio" name="correct_answer" value="{{ $opt['option'] ?? '' }}"
                                           {{ ($question->correct_answer == ($opt['option'] ?? '')) ? 'checked' : '' }}
                                           class="text-[#0777be] focus:ring-[#0777be]">

                                    <input type="text" name="options[{{ $index }}][option]" value="{{ $opt['option'] ?? '' }}"
                                           class="flex-1 border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 bg-[#0777be] text-white rounded-lg shadow hover:bg-[#0666a3]">Update Details</button>
                </div>
            </form>
        @endif

        {{-- 2. SETTINGS TAB --}}
        @if($activeTab == 'settings')
            <form action="{{ route($routePrefix . 'questions.update_settings', $question->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Skill</label>
                        <select name="skill_id" class="w-full border-gray-300 rounded-lg">
                            @foreach($skills as $skill)
                                <option value="{{ $skill->id }}" {{ $question->skill_id == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Topic</label>
                        <select name="topic_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select Topic</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ $question->topic_id == $topic->id ? 'selected' : '' }}>{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Difficulty Level</label>
                        <select name="difficulty_level_id" class="w-full border-gray-300 rounded-lg">
                            @foreach($difficultyLevels as $level)
                                <option value="{{ $level->id }}" {{ $question->difficulty_level_id == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Marks (+ve)</label>
                            <input type="number" step="0.25" name="default_marks" value="{{ $question->default_marks }}" class="w-full border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Time (Seconds)</label>
                            <input type="number" name="default_time" value="{{ $question->default_time }}" class="w-full border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 bg-[#f062a4] text-white rounded-lg shadow hover:bg-pink-600">Save Settings</button>
                </div>
            </form>
        @endif

        {{-- 3. SOLUTION TAB --}}
        @if($activeTab == 'solution')
            <form action="{{ route($routePrefix . 'questions.update_solution', $question->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Hint (Optional)</label>
                        <input type="text" name="hint" value="{{ $question->hint }}" class="w-full border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Detailed Solution</label>
                        <textarea name="solution" id="solution_editor" rows="5" class="w-full border-gray-300 rounded-lg">{{ $question->solution }}</textarea>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Video Solution (URL)</label>
                        <input type="url" name="solution_video" value="{{ $question->solution_video }}" class="w-full border-gray-300 rounded-lg" placeholder="https://youtube.com/...">
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 bg-[#0777be] text-white rounded-lg shadow hover:bg-[#0666a3]">Save Solution</button>
                </div>
            </form>
        @endif

        {{-- 4. ATTACHMENT TAB --}}
        @if($activeTab == 'attachment')
            <form action="{{ route($routePrefix . 'questions.update_attachment', $question->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="has_attachment" value="0" {{ !$question->has_attachment ? 'checked' : '' }} class="text-[#0777be]">
                            <span>No Attachment</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="has_attachment" value="1" {{ $question->has_attachment ? 'checked' : '' }} class="text-[#0777be]">
                            <span>With Attachment</span>
                        </label>
                    </div>

                    <div x-data="{ type: '{{ $question->attachment_type ?? 'comprehension' }}' }" x-show="{{ $question->has_attachment ? 'true' : 'false' }}">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Attachment Type</label>
                        <select name="attachment_type" x-model="type" class="w-full mb-4 border-gray-300 rounded-lg">
                            <option value="comprehension">Comprehension Passage</option>
                            <option value="audio">Audio</option>
                            <option value="video">Video</option>
                        </select>

                        {{-- Comprehension Select --}}
                        <div x-show="type === 'comprehension'">
                            <label class="block mb-1 text-sm font-medium text-gray-700">Select Passage</label>
                            <select name="comprehension_id" class="w-full border-gray-300 rounded-lg">
                                <option value="">Select a Passage</option>
                                {{-- Loop through passages --}}
                                @foreach($passages ?? [] as $passage)
                                    <option value="{{ $passage->id }}" {{ $question->comprehension_passage_id == $passage->id ? 'selected' : '' }}>{{ $passage->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 bg-[#f062a4] text-white rounded-lg shadow hover:bg-pink-600">Save Attachment</button>
                </div>
            </form>
        @endif

    </div>
</div>

{{-- Scripts for CKEditor --}}
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    if(document.getElementById('details_editor')) CKEDITOR.replace('details_editor');
    if(document.getElementById('solution_editor')) CKEDITOR.replace('solution_editor');
</script>
@endsection
