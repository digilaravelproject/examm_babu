@extends('layouts.admin')
@section('title', 'Configure Lessons - Step 1')

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4">

        {{-- Header --}}
        <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Configure Lessons</h1>
                <p class="text-sm text-gray-500">Add Lessons to Learning</p>
            </div>

            {{-- Stepper UI --}}
            <div class="flex gap-4">
                <div
                    class="flex items-center px-4 py-2 bg-white border-2 border-[#0777be] text-[#0777be] rounded-lg font-bold shadow-sm">
                    <span
                        class="w-6 h-6 flex items-center justify-center bg-[#0777be] text-white rounded-full text-xs mr-2">1</span>
                    Choose Skill
                </div>
                <div class="flex items-center px-4 py-2 bg-gray-50 border border-gray-200 text-gray-400 rounded-lg">
                    <span
                        class="w-6 h-6 flex items-center justify-center bg-gray-300 text-white rounded-full text-xs mr-2">2</span>
                    Add/Remove Lessons
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow border border-gray-200 p-8">
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-4 mb-6">Choose Sub Category & Skill</h2>

            <form action="{{ route('admin.practice.manage') }}" method="GET">
                <div class="space-y-6">
                    {{-- Sub Category --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Sub Category <span
                                class="text-red-500">*</span></label>
                        <select name="sub_category_id" required
                            class="w-full border-gray-300 rounded-lg p-3 focus:ring-[#0777be] focus:border-[#0777be]">
                            <option value="">Select Sub Category</option>
                            @foreach ($subCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Skill --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Skill <span
                                class="text-red-500">*</span></label>
                        <select name="skill_id" required
                            class="w-full border-gray-300 rounded-lg p-3 focus:ring-[#0777be] focus:border-[#0777be]">
                            <option value="">Select Skill</option>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit"
                            class="px-6 py-3 bg-[#0777be] text-white font-bold rounded-lg hover:bg-[#0666a3] transition-all">
                            PROCEED &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
