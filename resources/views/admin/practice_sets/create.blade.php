@extends('layouts.admin')
@section('title', 'Create Practice Set')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Create Practice Set</h3>
            <div class="text-sm text-gray-500">Step 1: Basic Information</div>
        </div>
        <div>
            <a href="{{ route('admin.practice-sets.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0777be]">
                &larr; Back to List
            </a>
        </div>
    </div>

    {{-- Stepper Tabs --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#0777be] text-white font-bold text-sm">1</span>
                <span class="font-semibold text-gray-900">Details</span>
            </div>
            <div class="hidden md:block w-full border-t border-gray-200 mx-4"></div>
            <div class="flex items-center gap-2 opacity-50">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-600 font-bold text-sm">2</span>
                <span class="font-semibold text-gray-600">Settings</span>
            </div>
            <div class="hidden md:block w-full border-t border-gray-200 mx-4"></div>
            <div class="flex items-center gap-2 opacity-50">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-600 font-bold text-sm">3</span>
                <span class="font-semibold text-gray-600">Questions</span>
            </div>
        </div>
    </div>

    {{-- Form Content --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 md:p-8">
            <form action="{{ route('admin.practice-sets.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-y-6 gap-x-4">

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Enter practice set title"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] p-2.5">
                        @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category & Skill --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Sub Category <span class="text-red-500">*</span></label>
                            <select name="sub_category_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] p-2.5">
                                <option value="">Select Category</option>
                                @foreach($subCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('sub_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('sub_category_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Skill <span class="text-red-500">*</span></label>
                            <select name="skill_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] p-2.5">
                                <option value="">Select Skill</option>
                                @foreach($skills as $skill)
                                    <option value="{{ $skill->id }}" {{ old('skill_id') == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                                @endforeach
                            </select>
                            @error('skill_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="4" placeholder="Describe this practice set..."
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] p-2.5">{{ old('description') }}</textarea>
                    </div>

                    {{-- Settings Toggles --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Paid Toggle --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Paid Content</label>
                                <p class="text-xs text-gray-500">Accessible only to premium users.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_paid" value="1" class="sr-only peer" {{ old('is_paid') ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                        {{-- Active Toggle --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Visibility Status</label>
                                <p class="text-xs text-gray-500">Enable to make it visible to users.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                    </div>

                </div>

                {{-- Footer Buttons --}}
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('admin.practice-sets.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-[#0777be] rounded-lg hover:bg-[#0666a3] shadow-md transition-all">Save & Next: Settings &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
