@extends('layouts.admin')
@section('header', 'Add New Stat')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
            <form action="{{ route('admin.home-stats.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Content --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Count Number</label>
                        <input type="text" name="count" class="w-full border rounded-lg px-3 py-2"
                            placeholder="e.g. 50,000+" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Label</label>
                        <input type="text" name="label" class="w-full border rounded-lg px-3 py-2"
                            placeholder="e.g. Happy Students" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon (Emoji)</label>
                        <input type="text" name="icon" class="w-full border rounded-lg px-3 py-2"
                            placeholder="e.g. 🏆" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" class="w-full border rounded-lg px-3 py-2" value="0">
                    </div>

                    {{-- Styling (Tailwind Classes) --}}
                    <div class="col-span-2 border-t pt-4 mt-2">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Color Configuration (Tailwind Classes)
                        </h3>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Text Color Class</label>
                        <select name="text_class" class="w-full border rounded-lg px-3 py-2 bg-white">
                            <option value="text-yellow-600">Yellow (text-yellow-600)</option>
                            <option value="text-brand-blue">Brand Blue (text-brand-blue)</option>
                            <option value="text-green-600">Green (text-green-600)</option>
                            <option value="text-orange-600">Orange (text-orange-600)</option>
                            <option value="text-purple-600">Purple (text-purple-600)</option>
                            <option value="text-red-600">Red (text-red-600)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Background Class</label>
                        <select name="bg_class" class="w-full border rounded-lg px-3 py-2 bg-white">
                            <option value="bg-yellow-100">Yellow Light (bg-yellow-100)</option>
                            <option value="bg-blue-100">Blue Light (bg-blue-100)</option>
                            <option value="bg-green-100">Green Light (bg-green-100)</option>
                            <option value="bg-orange-100">Orange Light (bg-orange-100)</option>
                            <option value="bg-purple-100">Purple Light (bg-purple-100)</option>
                            <option value="bg-red-100">Red Light (bg-red-100)</option>
                        </select>
                    </div>

                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('admin.home-stats.index') }}"
                        class="px-5 py-2.5 rounded-lg border text-gray-600 hover:bg-gray-50">Cancel</a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-bold">Save
                        Stat</button>
                </div>
            </form>
        </div>
    </div>
@endsection
