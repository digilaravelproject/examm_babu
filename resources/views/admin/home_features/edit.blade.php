@extends('layouts.admin')
@section('header', 'Edit Feature')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Edit Feature</h2>
                <a href="{{ route('admin.home-features.index') }}" class="text-gray-500 hover:text-gray-700">← Back to
                    List</a>
            </div>

            {{-- UPDATE Form --}}
            <form action="{{ route('admin.home-features.update', $feature->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- UPDATE ke liye zaroori hai --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Title</label>
                        <input type="text" name="title"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('title', $feature->title) }}" placeholder="e.g. Exam Oriented" required>
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon (Emoji)</label>
                        <input type="text" name="icon"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('icon', $feature->icon) }}" placeholder="e.g. 🎯" required>
                    </div>

                    {{-- Description --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            placeholder="Enter short description" required>{{ old('description', $feature->description) }}</textarea>
                    </div>

                    {{-- Background Color Dropdown --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon Background Color</label>
                        <select name="bg_class"
                            class="w-full border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500">
                            <option value="bg-blue-100" {{ $feature->bg_class == 'bg-blue-100' ? 'selected' : '' }}>Blue
                                Light (bg-blue-100)</option>
                            <option value="bg-green-100" {{ $feature->bg_class == 'bg-green-100' ? 'selected' : '' }}>Green
                                Light (bg-green-100)</option>
                            <option value="bg-purple-100" {{ $feature->bg_class == 'bg-purple-100' ? 'selected' : '' }}>
                                Purple Light (bg-purple-100)</option>
                            <option value="bg-orange-100" {{ $feature->bg_class == 'bg-orange-100' ? 'selected' : '' }}>
                                Orange Light (bg-orange-100)</option>
                            <option value="bg-red-100" {{ $feature->bg_class == 'bg-red-100' ? 'selected' : '' }}>Red Light
                                (bg-red-100)</option>
                            <option value="bg-yellow-100" {{ $feature->bg_class == 'bg-yellow-100' ? 'selected' : '' }}>
                                Yellow Light (bg-yellow-100)</option>
                        </select>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('sort_order', $feature->sort_order) }}">
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="mt-8 flex justify-end gap-3 border-t pt-4">
                    <a href="{{ route('admin.home-features.index') }}"
                        class="px-5 py-2.5 rounded-lg border text-gray-600 hover:bg-gray-50 font-medium">Cancel</a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-bold shadow-lg transform transition hover:-translate-y-0.5">
                        Update Feature
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
