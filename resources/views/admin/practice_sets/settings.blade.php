@extends('layouts.admin')
@section('title', 'Practice Set Settings')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Practice Set Settings</h3>
            <div class="text-sm text-gray-500">Configuration for: {{ $practiceSet->title }}</div>
        </div>
        <div>
            <a href="{{ route('admin.practice-sets.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                &larr; Back to List
            </a>
        </div>
    </div>

    {{-- Stepper Tabs --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Step 1: Available --}}
            <a href="{{ route('admin.practice-sets.edit', $practiceSet->id) }}" class="flex items-center gap-2 group cursor-pointer">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#0777be] text-white font-bold text-sm"><i class="fas fa-check"></i></span>
                <span class="font-semibold text-gray-900 group-hover:text-[#0777be]">Details</span>
            </a>
            <div class="hidden md:block w-full border-t-2 border-[#0777be] mx-4"></div>

            {{-- Step 2: Active --}}
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#0777be] text-white font-bold text-sm">2</span>
                <span class="font-semibold text-gray-900">Settings</span>
            </div>
            <div class="hidden md:block w-full border-t border-gray-200 mx-4"></div>

            {{-- Step 3: Next --}}
            <div class="flex items-center gap-2 opacity-50">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-600 font-bold text-sm">3</span>
                <span class="font-semibold text-gray-600">Questions</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ mode: '{{ $practiceSet->auto_grading ? 'auto' : 'manual' }}' }">
        <div class="p-6 md:p-8">
            <form action="{{ route('admin.practice-sets.settings.update', $practiceSet->id) }}" method="POST">
                @csrf

                <h4 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2">Grading & Points</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    {{-- Allow Rewards --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allow Rewards</label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="allow_rewards" value="1" class="text-[#0777be] focus:ring-[#0777be]" {{ $practiceSet->allow_rewards ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="allow_rewards" value="0" class="text-[#0777be] focus:ring-[#0777be]" {{ !$practiceSet->allow_rewards ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">No</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Users earn points for correct answers.</p>
                    </div>

                    {{-- Reward Popup --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Show Reward Popup</label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="show_reward_popup" value="1" class="rounded text-[#0777be] focus:ring-[#0777be]"
                                    {{ isset($practiceSet->settings['show_reward_popup']) && $practiceSet->settings['show_reward_popup'] ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Enable Animation</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Grading Mode --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-3">Grading Mode</label>
                            <div class="flex space-x-4">
                                <button type="button" @click="mode = 'auto'"
                                    :class="mode === 'auto' ? 'bg-[#0777be] text-white border-[#0777be]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    class="px-4 py-2 border rounded-md text-sm font-medium transition-all w-full md:w-auto">
                                    Auto (Default Marks)
                                </button>
                                <button type="button" @click="mode = 'manual'"
                                    :class="mode === 'manual' ? 'bg-[#0777be] text-white border-[#0777be]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    class="px-4 py-2 border rounded-md text-sm font-medium transition-all w-full md:w-auto">
                                    Manual (Custom)
                                </button>
                            </div>
                            <input type="hidden" name="auto_grading" :value="mode === 'auto' ? 1 : 0">
                        </div>

                        {{-- Correct Marks (Only Manual) --}}
                        <div x-show="mode === 'manual'" x-transition class="mt-4 md:mt-0">
                            <label class="block text-sm font-bold text-gray-800 mb-2">Marks per Question</label>
                            <input type="number" step="0.5" name="correct_marks" value="{{ $practiceSet->correct_marks }}"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] p-2.5">
                            <p class="text-xs text-gray-500 mt-1">Assign equal marks for all questions in this set.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-3">
                    <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md transition-all">
                        Save Settings & Finish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
