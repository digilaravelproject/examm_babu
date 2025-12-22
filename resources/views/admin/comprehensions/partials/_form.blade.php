@php
    $isEdit = $passage->exists;
    $action = $isEdit ? route('admin.comprehensions.update', $passage->id) : route('admin.comprehensions.store');
@endphp

<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden font-sans">

    <form action="{{ $action }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- HEADER (Compact) --}}
        <div class="bg-gray-50/80 backdrop-blur px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-extrabold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                <span class="text-xl">📝</span>
                {{ $isEdit ? 'Edit Passage' : 'Create New Passage' }}
            </h3>
            @if($isEdit)
                <span class="px-2.5 py-0.5 bg-blue-50 text-[#0777be] text-[10px] font-bold rounded border border-blue-100 font-mono tracking-wider">
                    {{ $passage->code }}
                </span>
            @endif
        </div>

        <div class="p-5 space-y-5">

            {{-- ROW 1: TITLE & STATUS (Side by Side) --}}
            <div class="flex flex-col md:flex-row gap-5 items-start">

                {{-- Title Input (Flex Grow) --}}
                <div class="flex-1 w-full space-y-1.5">
                    <label class="block text-gray-600 font-bold text-xs uppercase tracking-wide">
                        Passage Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $passage->title) }}"
                           class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium text-gray-800"
                           placeholder="Enter passage title...">
                    @error('title') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Status Toggle (Compact) --}}
                <div class="w-full md:w-auto space-y-1.5">
                    <label class="block text-gray-600 font-bold text-xs uppercase tracking-wide">Status</label>
                    <label class="cursor-pointer flex items-center justify-between p-2.5 border border-gray-200 rounded-lg hover:bg-gray-50 transition w-full md:w-40 bg-white">
                        <span class="text-xs font-bold text-gray-700">Active?</span>
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $passage->is_active ?? 1) ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#94c940]"></div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- ROW 2: BODY (TinyMCE) --}}
            <div class="space-y-1.5">
                <label class="block text-gray-600 font-bold text-xs uppercase tracking-wide flex items-center gap-2">
                    Content Body <span class="text-red-500">*</span>
                </label>
                <div class="rounded-lg overflow-hidden border border-gray-300 shadow-sm focus-within:border-[#0777be] focus-within:ring-1 focus-within:ring-[#0777be] transition-all">
                    {{-- Reduced height slightly for concise view --}}
                    <textarea name="body" id="editor_body" class="w-full h-[400px] opacity-0">{{ old('body', $passage->body) }}</textarea>
                </div>
                @error('body') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>

        </div>

        {{-- COMPACT FOOTER --}}
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end gap-3 items-center">
            <a href="{{ route('admin.comprehensions.index') }}" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-800 uppercase tracking-wide transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-[#0777be] text-white rounded-lg shadow hover:bg-[#0666a3] font-bold text-xs uppercase tracking-wide transform hover:-translate-y-0.5 transition-all">
                {{ $isEdit ? 'Update Passage' : 'Save Passage' }}
            </button>
        </div>
    </form>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.1.0/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    window.onload = function() {
        tinymce.init({
            selector: '#editor_body',
            height: 380, // Concise Height
            menubar: false,
            statusbar: false, // Hides bottom bar for cleaner look
            plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen table help wordcount',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | fullscreen',
            content_style: 'body { font-family:Inter,sans-serif; font-size:14px; color:#374151; padding:10px }',
            border_widths: { top: 0, right: 0, bottom: 0, left: 0 },
        });
    };
</script>

<style>
    /* Clean TinyMCE */
    .tox-tinymce { border: none !important; }
</style>
