@extends('layouts.admin')

@section('title', 'Create Question')
@section('header', 'Create New Question')

@php
    $routePrefix = request()->routeIs('instructor.*') ? 'instructor.' : 'admin.';
@endphp

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Question</h1>
            <p class="text-sm text-gray-500">
                Type: <span class="font-semibold text-[#0777be]">{{ $questionType->name }} ({{ $questionType->code }})</span>
            </p>
        </div>
        <a href="{{ route($routePrefix . 'questions.index') }}" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
        </a>
    </div>

    {{-- Form --}}
    <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
        <form action="{{ route($routePrefix . 'questions.store') }}" method="POST">
            @csrf
            <input type="hidden" name="question_type_id" value="{{ $questionType->id }}">

            <div class="grid grid-cols-1 gap-6">

                {{-- Question Text --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Question Text <span class="text-red-500">*</span></label>
                    <textarea name="question" id="question_editor" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be]">{{ old('question') }}</textarea>
                    @error('question') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Skill Selection --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Skill / Subject <span class="text-red-500">*</span></label>
                    <select name="skill_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be]">
                        <option value="">Select Skill</option>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" {{ old('skill_id') == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                        @endforeach
                    </select>
                    @error('skill_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Options Logic (Simple Initial Setup) --}}
                @if(in_array($questionType->code, ['MSA', 'MMA', 'TF']))
                    <div x-data="{ options: {{ json_encode($defaultOptions) }} }">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Options</label>
                        <template x-for="(opt, index) in options" :key="index">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 text-sm text-gray-400" x-text="index + 1 + '.'"></span>
                                <input type="text" :name="'options[' + index + '][option]'" x-model="opt.option" class="flex-1 border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]" placeholder="Option text">
                                <input type="hidden" :name="'options[' + index + '][id]'" :value="index">
                            </div>
                        </template>
                        <p class="mt-1 text-xs text-gray-500">You can add more detailed options and set the correct answer in the next step.</p>
                    </div>
                @endif

            </div>

            <div class="flex justify-end mt-8">
                <button type="submit" class="px-6 py-2.5 bg-[#0777be] text-white font-medium rounded-lg shadow-md hover:bg-[#0666a3] transition">
                    Save & Continue Setup &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

{{-- CKEditor Script (Optional) --}}
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('question_editor');
</script>
@endsection
