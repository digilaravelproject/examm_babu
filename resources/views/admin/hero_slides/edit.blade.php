@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white shadow-md rounded-lg p-6">

        {{-- Header with Back Button --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Edit Hero Slide</h2>
            <a href="{{ route('admin.hero-slides.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                ← Back to List
            </a>
        </div>

        {{-- UPDATE Form --}}
        <form action="{{ route('admin.hero-slides.update', $slide->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Zaroori: PUT method for Update --}}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Text Content --}}
                <div class="col-span-2">
                    <label class="block text-gray-700 font-bold mb-2">Title</label>
                    <input type="text" name="title" class="w-full border rounded px-3 py-2"
                           value="{{ old('title', $slide->title) }}" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Badge Text</label>
                    <input type="text" name="badge_text" class="w-full border rounded px-3 py-2"
                           value="{{ old('badge_text', $slide->badge_text) }}" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Sort Order</label>
                    <input type="number" name="sort_order" class="w-full border rounded px-3 py-2"
                           value="{{ old('sort_order', $slide->sort_order) }}">
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 font-bold mb-2">Description</label>
                    <textarea name="description" class="w-full border rounded px-3 py-2" rows="2" required>{{ old('description', $slide->description) }}</textarea>
                </div>

                {{-- Button Settings --}}
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Button Text</label>
                    <input type="text" name="button_text" class="w-full border rounded px-3 py-2"
                           value="{{ old('button_text', $slide->button_text) }}" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Button Link</label>
                    <input type="text" name="button_link" class="w-full border rounded px-3 py-2"
                           value="{{ old('button_link', $slide->button_link) }}" required>
                </div>

                {{-- Colors & Styling --}}
                <div class="col-span-2 border-t mt-4 pt-4">
                    <h3 class="font-bold text-gray-500 mb-4">Design & Colors</h3>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Theme Color (Text)</label>
                    <input type="text" name="theme_color" class="w-full border rounded px-3 py-2"
                           value="{{ old('theme_color', $slide->theme_color) }}" required>
                    <small class="text-gray-500">Ex: var(--brand-blue) or #ff0000</small>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Gradient Start</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="bg_gradient_start" class="w-full border rounded px-3 py-2"
                                   value="{{ old('bg_gradient_start', $slide->bg_gradient_start) }}" required>
                            {{-- Color Preview --}}
                            <div class="w-8 h-8 rounded border shadow-sm" style="background: {{ $slide->bg_gradient_start }}"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Gradient End</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="bg_gradient_end" class="w-full border rounded px-3 py-2"
                                   value="{{ old('bg_gradient_end', $slide->bg_gradient_end) }}" required>
                            {{-- Color Preview --}}
                            <div class="w-8 h-8 rounded border shadow-sm" style="background: {{ $slide->bg_gradient_end }}"></div>
                        </div>
                    </div>
                </div>

                {{-- Icons --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Top Icon (Emoji)</label>
                        <input type="text" name="icon_top" class="w-full border rounded px-3 py-2"
                               value="{{ old('icon_top', $slide->icon_top) }}" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Bottom Icon (Emoji)</label>
                        <input type="text" name="icon_bottom" class="w-full border rounded px-3 py-2"
                               value="{{ old('icon_bottom', $slide->icon_bottom) }}" required>
                    </div>
                </div>

            </div>

            <div class="mt-8 border-t pt-4">
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700 shadow-lg transform transition hover:-translate-y-0.5">
                    Update Slide
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
