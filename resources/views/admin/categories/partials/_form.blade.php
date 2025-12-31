@php
    $isEdit = $category->exists;
    $action = $isEdit ? route('admin.categories.update', $category->id) : route('admin.categories.store');
@endphp

<div class="max-w-5xl mx-auto overflow-hidden font-sans bg-white border border-gray-200 shadow-lg rounded-xl">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" x-data="imagePreview()">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-wide text-gray-800 uppercase">
                <span class="text-xl">📁</span>
                {{ $isEdit ? 'Edit Category' : 'Create New Category' }}
            </h3>
        </div>

        {{-- Error Display Block --}}
        @if ($errors->any())
            <div class="px-6 pt-4">
                <div class="p-4 text-red-700 bg-red-100 border border-red-400 rounded-lg">
                    <ul class="pl-5 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Category Name --}}
                <div class="space-y-1.5 {{ $isEdit ? '' : 'md:col-span-2' }}">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Category Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                        class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium">
                </div>

                {{-- Category Code (Visible only on Edit) --}}
                @if ($isEdit)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold tracking-wide text-gray-400 uppercase">Category Code (Read
                            Only)</label>
                        <div
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2.5 text-sm font-mono text-gray-500">
                            {{ $category->code }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Image & Status --}}
            <div class="grid items-start grid-cols-1 gap-8 md:grid-cols-2">
                <div class="space-y-3">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Category Icon</label>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-20 h-20 overflow-hidden border-2 border-gray-300 border-dashed rounded-xl bg-gray-50">
                            {{-- Preview Image --}}
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="object-cover w-full h-full">
                            </template>

                            {{-- Placeholder Text --}}
                            <template x-if="!imageUrl">
                                <span class="text-[10px] text-gray-400">No Image</span>
                            </template>
                        </div>
                        <div class="flex-1">
                            {{-- CHANGE: name="image_path" to match Request and Controller --}}
                            <input type="file" name="image_path" @change="fileChosen" accept="image/*"
                                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0777be] hover:file:bg-blue-100 transition">
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Status</label>
                    <label
                        class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm cursor-pointer">
                        <span class="text-sm font-bold text-gray-700">Active?</span>
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $category->is_active ?? 1) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#94c940] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full">
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Short Description --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Short Description</label>
                <textarea name="short_description" rows="2"
                    class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm">{{ old('short_description', $category->short_description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <a href="{{ route('admin.categories.index') }}"
                class="px-4 py-2 text-xs font-bold tracking-wide text-gray-500 uppercase">Cancel</a>
            <button type="submit"
                class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg shadow hover:bg-[#0666a3] font-bold text-xs uppercase tracking-wide transition-all">
                {{ $isEdit ? 'Update' : 'Save' }} Category
            </button>
        </div>
    </form>
</div>

<script>
    function imagePreview() {
        return {
            imageUrl: '{{ $category->image_path ? asset($category->image_path) : '' }}',
            fileChosen(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imageUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
