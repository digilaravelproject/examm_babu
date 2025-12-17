{{-- resources/views/admin/quiz_types/index.blade.php --}}
@extends('layouts.admin')

@section('header', 'Quiz Types')

@section('content')
<div class="max-w-full">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-semibold text-gray-800">Quiz Types</h3>

        <button id="toggleDrawerBtn"
            class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded shadow" type="button">
            NEW QUIZ TYPE
        </button>
    </div>

    <!-- TABLE -->
    <div class="bg-white p-6 rounded shadow relative">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-600">
                        <th class="py-3 px-4">CODE</th>
                        <th class="py-3 px-4">NAME</th>
                        <th class="py-3 px-4">STATUS</th>
                        <th class="py-3 px-4 text-right">ACTIONS</th>
                    </tr>

                    <!-- FILTERS -->
                    <tr class="bg-gray-50">
                        <form method="GET">
                            <th class="py-3 px-4">
                                <input name="code" value="{{ request('code') }}" placeholder="Search Code"
                                       class="w-full px-3 py-2 border rounded text-sm bg-white" />
                            </th>
                            <th class="py-3 px-4">
                                <input name="name" value="{{ request('name') }}" placeholder="Search Name"
                                       class="w-full px-3 py-2 border rounded text-sm bg-white" />
                            </th>
                            <th class="py-3 px-4">
                                <select name="status" class="w-full px-3 py-2 border rounded text-sm bg-white">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>In-active</option>
                                </select>
                            </th>
                            <th class="py-3 px-4 text-right">
                                <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm" type="submit">Filter</button>
                                <a href="{{ route('admin.quiz-types.index') }}" class="px-3 py-2 border rounded text-sm">Reset</a>
                            </th>
                        </form>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($quizTypes ?? [] as $qt)
                        <tr>
                            <td class="py-4 px-4">
                                <span class="inline-block bg-blue-500 text-white px-3 py-1 rounded-full text-xs">
                                    {{ $qt->code ?? 'qtp_'.substr(md5($qt->id ?? rand()),0,8) }}
                                </span>
                            </td>
                            <td class="py-4 px-4">{{ $qt->name }}</td>
                            <td class="py-4 px-4">
                                @if(($qt->status ?? 'inactive') == 'active')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded text-sm">Active</span>
                                @else
                                    <span class="bg-pink-100 text-pink-700 px-3 py-1 rounded text-sm">In-active</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <button class="px-3 py-2 border rounded" type="button">Actions</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-6 text-gray-600">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- BACKDROP -->
<div id="drawerBackdrop" class="hidden fixed inset-0 bg-black bg-opacity-40 z-40"></div>

<!-- DRAWER PANEL -->
<?php /*<div id="drawerPanel"
     class="fixed top-0 right-0 w-96 h-full bg-white z-50 shadow-lg transform translate-x-full transition-all duration-300">

    <div class="p-6 overflow-y-auto h-full">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">New Quiz Type</h2>

            <!-- NOTE: type="button" prevents accidental form submission -->
            <button id="drawerCloseBtn" class="p-2 border rounded-full" type="button" aria-label="Close drawer">
                <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- FORM -->
        <form method="POST" action="#" id="quizTypeForm" novalidate>
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium">Quiz Type Name</label>
                <input name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border rounded" />
                @error('name') <div class="text-xs text-red-600 mt-1 server-error">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Color</label>
                <div class="flex items-center gap-3">
                    <div id="colorPreview" style="width:36px;height:28px;border-radius:4px;border:1px solid #ddd;background:{{ old('color','#ff0000') }}"></div>
                    <input id="colorInput" type="color" name="color" value="{{ old('color','#ff0000') }}" class="h-10 w-12 border rounded cursor-pointer">
                    <input id="colorHex" type="text" value="{{ old('color','#ff0000') }}" placeholder="#rrggbb" class="flex-1 px-3 py-2 border rounded text-sm" name="color_hex">
                </div>
                @error('color') <div class="text-xs text-red-600 mt-1 server-error">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Image URL</label>
                <input name="image_url" value="{{ old('image_url') }}" placeholder="https://..." class="w-full px-3 py-2 border rounded" />
                @error('image_url') <div class="text-xs text-red-600 mt-1 server-error">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2 border rounded">{{ old('description') }}</textarea>
                @error('description') <div class="text-xs text-red-600 mt-1 server-error">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center justify-between mb-6">
                <div>
                    <label class="block text-sm font-medium">Active</label>
                    <p class="text-xs text-gray-500">Active (Shown Everywhere). In-active (Hidden Everywhere).</p>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input id="statusToggle" type="checkbox" name="status" value="active" class="sr-only" {{ old('status')=='active' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-blue-500 transition"></div>
                    <div id="statusDot" class="absolute w-5 h-5 bg-white rounded-full left-1 top-0.5 transition-all"></div>
                </label>
            </div>

            <div class="flex gap-3">
                <button class="px-4 py-2 bg-blue-600 text-white rounded flex-1" type="submit">Create</button>
                <button id="drawerCancelBtn" type="button" class="px-4 py-2 border rounded">Cancel</button>
            </div>
        </form>
    </div>
</div> */?>

<!-- JAVASCRIPT: robust toggle, close, reset form -->
<script>
    (function () {
        const openBtn = document.getElementById('toggleDrawerBtn');
        const closeBtn = document.getElementById('drawerCloseBtn');
        const cancelBtn = document.getElementById('drawerCancelBtn');
        const drawer = document.getElementById('drawerPanel');
        const backdrop = document.getElementById('drawerBackdrop');
        const form = document.getElementById('quizTypeForm');
        const colorInput = document.getElementById('colorInput');
        const colorHex = document.getElementById('colorHex');
        const colorPreview = document.getElementById('colorPreview');

        // helper: is drawer open?
        function isOpen() {
            return !drawer.classList.contains('translate-x-full');
        }

        // open drawer
        function openDrawer() {
            backdrop.classList.remove('hidden');
            // allow small tick for transition to work if hidden -> visible
            setTimeout(() => {
                drawer.classList.remove('translate-x-full');
                drawer.classList.add('translate-x-0');
                backdrop.classList.add('opacity-100');
            }, 10);
            // ensure preview color sync if present
            syncColorInputs();
            // focus first input
            const first = form.querySelector('input[name="name"]');
            if (first) first.focus();
        }

        // close drawer + reset form
        function closeDrawer(resetForm = true) {
            backdrop.classList.remove('opacity-100');
            drawer.classList.remove('translate-x-0');
            drawer.classList.add('translate-x-full');

            // hide the backdrop after transition
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 220);

            // reset/clear the form fields (but do not clear server-side rendered error messages)
            if (resetForm && form) {
                form.reset();

                // color inputs: set default so preview is visible
                if (colorInput) colorInput.value = '#ff0000';
                if (colorHex) colorHex.value = '#ff0000';
                if (colorPreview) colorPreview.style.background = '#ff0000';
            }
        }

        // toggle drawer open/close
        function toggleDrawer() {
            if (isOpen()) closeDrawer();
            else openDrawer();
        }

        // sync color picker <-> text input <-> preview square
        function syncColorInputs() {
            if (!colorInput || !colorHex || !colorPreview) return;
            // initial sync
            colorPreview.style.background = colorInput.value || colorHex.value || '#ff0000';
            // color input changes
            colorInput.addEventListener('input', function () {
                colorHex.value = this.value;
                colorPreview.style.background = this.value;
            });
            // hex changes
            colorHex.addEventListener('input', function () {
                let v = this.value.trim();
                if (v && v[0] !== '#') v = '#' + v;
                // if valid hex (3 or 6), update picker, else just set preview to raw input
                if (/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(v)) {
                    colorInput.value = v;
                    colorPreview.style.background = v;
                } else {
                    colorPreview.style.background = this.value || '#ffffff';
                }
            });
        }

        // attach events
        if (openBtn) openBtn.addEventListener('click', function (e) {
            e.preventDefault();
            toggleDrawer();
        });

        if (closeBtn) closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeDrawer(true); // close and reset
        });

        if (cancelBtn) cancelBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeDrawer(true);
        });

        if (backdrop) backdrop.addEventListener('click', function () {
            closeDrawer(true);
        });

        // close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen()) {
                closeDrawer(true);
            }
        });

        // If validation errors exist (server-side), keep drawer open so user can see them.
        @if($errors->any() || old('name') || old('description') || old('image_url') || old('color') || old('status'))
            // open drawer after page load so Blade-rendered errors are visible
            setTimeout(function () {
                openDrawer();
            }, 80);
        @endif

        // initialize color sync (if drawer already open)
        syncColorInputs();
    })();
</script>

@endsection
