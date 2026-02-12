@extends('layouts.admin')
@section('header', 'Add Feature')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
            <form action="{{ route('admin.home-features.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" class="w-full border rounded-lg px-3 py-2"
                            placeholder="e.g. Exam Oriented" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon (Emoji)</label>
                        <input type="text" name="icon" class="w-full border rounded-lg px-3 py-2"
                            placeholder="e.g. 🎯" required>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2"
                            placeholder="Enter short description" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon Background Color</label>
                        <select name="bg_class" class="w-full border rounded-lg px-3 py-2 bg-white">
                            <option value="bg-blue-100">Blue Light (bg-blue-100)</option>
                            <option value="bg-green-100">Green Light (bg-green-100)</option>
                            <option value="bg-purple-100">Purple Light (bg-purple-100)</option>
                            <option value="bg-orange-100">Orange Light (bg-orange-100)</option>
                            <option value="bg-red-100">Red Light (bg-red-100)</option>
                            <option value="bg-yellow-100">Yellow Light (bg-yellow-100)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" class="w-full border rounded-lg px-3 py-2" value="0">
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-4">
                    <a href="{{ route('admin.home-features.index') }}"
                        class="px-5 py-2.5 rounded-lg border text-gray-600 hover:bg-gray-50">Cancel</a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-bold">Save
                        Feature</button>
                </div>
            </form>
        </div>
    </div>
@endsection
