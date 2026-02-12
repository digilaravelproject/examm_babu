@extends('layouts.admin')
@section('title', 'Edit Practice Set')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Edit Practice Set</h3>
                <div class="text-sm text-gray-500">{{ $practiceSet->title }}</div>
            </div>
            <div>
                <a href="{{ route('admin.practice-sets.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    &larr; Back to List
                </a>
            </div>
        </div>

        {{-- Stepper Tabs --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Step 1: Active --}}
                <a href="{{ route('admin.practice-sets.edit', $practiceSet->id) }}"
                    class="flex items-center gap-2 group cursor-pointer">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-[#0777be] text-white font-bold text-sm">1</span>
                    <span class="font-semibold text-gray-900 group-hover:text-[#0777be]">Details</span>
                </a>
                <div class="hidden md:block w-full border-t-2 border-[#0777be] mx-4"></div>

                {{-- Step 2: Available --}}
                <a href="{{ route('admin.practice-sets.settings', $practiceSet->id) }}"
                    class="flex items-center gap-2 group cursor-pointer">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-white border-2 border-gray-300 text-gray-500 font-bold text-sm group-hover:border-[#0777be] group-hover:text-[#0777be]">2</span>
                    <span class="font-semibold text-gray-500 group-hover:text-[#0777be]">Settings</span>
                </a>
                <div class="hidden md:block w-full border-t border-gray-200 mx-4"></div>

                {{-- Step 3: Placeholder (Questions) --}}
                <div class="flex items-center gap-2 opacity-50">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-600 font-bold text-sm">3</span>
                    <span class="font-semibold text-gray-600">Questions</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('admin.practice-sets.update', $practiceSet->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4">

                        {{-- Fields same as Create, but with values --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Title <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $practiceSet->title) }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] p-2.5">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Sub Category <span
                                        class="text-red-500">*</span></label>
                                <select name="sub_category_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2.5">
                                    @foreach ($subCategories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ $practiceSet->sub_category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Skill <span
                                        class="text-red-500">*</span></label>
                                <select name="skill_id" class="w-full border-gray-300 rounded-lg shadow-sm p-2.5">
                                    @foreach ($skills as $skill)
                                        <option value="{{ $skill->id }}"
                                            {{ $practiceSet->skill_id == $skill->id ? 'selected' : '' }}>
                                            {{ $skill->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm p-2.5">{{ old('description', $practiceSet->description) }}</textarea>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center justify-between">
                                <div><label class="block text-sm font-bold text-gray-700">Paid Content</label></div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_paid" value="1" class="sr-only peer"
                                        {{ $practiceSet->is_paid ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#0777be] peer-checked:after:translate-x-full after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div><label class="block text-sm font-bold text-gray-700">Visibility Status</label></div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                        {{ $practiceSet->is_active ? 'checked' : '' }}>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:bg-green-600 peer-checked:after:translate-x-full after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-3">
                        <button type="submit"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-[#0777be] rounded-lg hover:bg-[#0666a3] shadow-md transition-all">Update
                            Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
