@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold mb-6">Add New Slide</h2>

            <form action="{{ route('admin.hero-slides.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Text Content --}}
                    <div class="col-span-2">
                        <label class="block text-gray-700 font-bold mb-2">Title</label>
                        <input type="text" name="title" class="w-full border rounded px-3 py-2"
                            placeholder="e.g. SSC CGL 2025" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Badge Text</label>
                        <input type="text" name="badge_text" class="w-full border rounded px-3 py-2"
                            placeholder="e.g. TRENDING NOW" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Sort Order</label>
                        <input type="number" name="sort_order" class="w-full border rounded px-3 py-2" value="0">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-gray-700 font-bold mb-2">Description</label>
                        <textarea name="description" class="w-full border rounded px-3 py-2" rows="2" placeholder="Short description..."
                            required></textarea>
                    </div>

                    {{-- Button Settings --}}
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Button Text</label>
                        <input type="text" name="button_text" class="w-full border rounded px-3 py-2"
                            placeholder="e.g. View Series" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Button Link</label>
                        <input type="text" name="button_link" class="w-full border rounded px-3 py-2" placeholder="#"
                            required>
                    </div>

                    {{-- Colors & Styling --}}
                    <div class="col-span-2 border-t mt-4 pt-4">
                        <h3 class="font-bold text-gray-500 mb-4">Design & Colors</h3>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Theme Color (Text)</label>
                        <input type="text" name="theme_color" class="w-full border rounded px-3 py-2"
                            placeholder="var(--brand-blue) or #ff0000" required>
                        <small class="text-gray-500">Used for button text color</small>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Gradient Start</label>
                            <input type="text" name="bg_gradient_start" class="w-full border rounded px-3 py-2"
                                placeholder="var(--brand-blue)" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Gradient End</label>
                            <input type="text" name="bg_gradient_end" class="w-full border rounded px-3 py-2"
                                placeholder="#60a5fa" required>
                        </div>
                    </div>

                    {{-- Icons --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Top Icon (Emoji)</label>
                            <input type="text" name="icon_top" class="w-full border rounded px-3 py-2" placeholder="🏛️"
                                required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Bottom Icon (Emoji)</label>
                            <input type="text" name="icon_bottom" class="w-full border rounded px-3 py-2"
                                placeholder="🇮🇳" required>
                        </div>
                    </div>

                </div>

                <div class="mt-8">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">
                        Save Slide
                    </button>
                    <a href="{{ route('admin.hero-slides.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
