@extends('layouts.admin')
@section('header', 'Edit Stat')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Edit Statistic</h2>
                <a href="{{ route('admin.home-stats.index') }}" class="text-gray-500 hover:text-gray-700">← Back to List</a>
            </div>

            {{-- UPDATE Form --}}
            <form action="{{ route('admin.home-stats.update', $stat->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- UPDATE ke liye zaroori hai --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Count --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Count Number</label>
                        <input type="text" name="count"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('count', $stat->count) }}" required>
                    </div>

                    {{-- Label --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Label</label>
                        <input type="text" name="label"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('label', $stat->label) }}" required>
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon (Emoji)</label>
                        <input type="text" name="icon"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('icon', $stat->icon) }}" required>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value="{{ old('sort_order', $stat->sort_order) }}">
                    </div>

                    {{-- Styling Header --}}
                    <div class="col-span-2 border-t pt-4 mt-2">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Color Configuration</h3>
                    </div>

                    {{-- Text Color Dropdown --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Text Color Class</label>
                        <select name="text_class"
                            class="w-full border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500">
                            <option value="text-yellow-600" {{ $stat->text_class == 'text-yellow-600' ? 'selected' : '' }}>
                                Yellow</option>
                            <option value="text-brand-blue" {{ $stat->text_class == 'text-brand-blue' ? 'selected' : '' }}>
                                Brand Blue</option>
                            <option value="text-green-600" {{ $stat->text_class == 'text-green-600' ? 'selected' : '' }}>
                                Green</option>
                            <option value="text-orange-600" {{ $stat->text_class == 'text-orange-600' ? 'selected' : '' }}>
                                Orange</option>
                            <option value="text-purple-600" {{ $stat->text_class == 'text-purple-600' ? 'selected' : '' }}>
                                Purple</option>
                            <option value="text-red-600" {{ $stat->text_class == 'text-red-600' ? 'selected' : '' }}>Red
                            </option>
                        </select>
                    </div>

                    {{-- Background Color Dropdown --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Background Class</label>
                        <select name="bg_class"
                            class="w-full border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500">
                            <option value="bg-yellow-100" {{ $stat->bg_class == 'bg-yellow-100' ? 'selected' : '' }}>Yellow
                                Light</option>
                            <option value="bg-blue-100" {{ $stat->bg_class == 'bg-blue-100' ? 'selected' : '' }}>Blue Light
                            </option>
                            <option value="bg-green-100" {{ $stat->bg_class == 'bg-green-100' ? 'selected' : '' }}>Green
                                Light</option>
                            <option value="bg-orange-100" {{ $stat->bg_class == 'bg-orange-100' ? 'selected' : '' }}>Orange
                                Light</option>
                            <option value="bg-purple-100" {{ $stat->bg_class == 'bg-purple-100' ? 'selected' : '' }}>Purple
                                Light</option>
                            <option value="bg-red-100" {{ $stat->bg_class == 'bg-red-100' ? 'selected' : '' }}>Red Light
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="mt-8 flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.home-stats.index') }}"
                        class="px-5 py-2.5 rounded-lg border text-gray-600 hover:bg-gray-50 font-medium">Cancel</a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-bold shadow-lg transform transition hover:-translate-y-0.5">
                        Update Stat
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
