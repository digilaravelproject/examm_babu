@extends('layouts.admin')

@section('title', 'Edit Sub-Category')
@section('header', 'Edit Sub-Category')

@section('content')
    <div class="max-w-5xl mx-auto overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl"
        x-data="imagePreview()">
        <form action="{{ route('admin.sub-categories.update', $subCategory->id) }}" method="POST"
            enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Error Display --}}
            @if ($errors->any())
                <div class="p-4 text-red-700 bg-red-100 border border-red-400 rounded-lg">
                    <ul class="pl-5 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Parent Category *</label>
                    <select name="category_id" required
                        class="w-full text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                        <option value="">Select Parent</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $subCategory->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Sub-Category Type *</label>
                    <select name="sub_category_type_id" required
                        class="w-full text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                        <option value="">Select Type</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->id }}"
                                {{ old('sub_category_type_id', $subCategory->sub_category_type_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-gray-600 uppercase">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $subCategory->name) }}" required
                        class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-[#0777be]">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Image Upload --}}
                <div class="space-y-3">
                    <label class="text-xs font-bold text-gray-600 uppercase">Icon/Image</label>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-16 h-16 overflow-hidden border-2 border-dashed rounded-lg bg-gray-50">
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="object-cover w-full h-full">
                            </template>
                            <template x-if="!imageUrl">
                                <span class="text-[10px] text-gray-400">Preview</span>
                            </template>
                        </div>

                        {{-- Input Name: image_path --}}
                        <input type="file" name="image_path" @change="fileChosen" accept="image/*"
                            class="text-xs file:bg-blue-50 file:border-0 file:rounded-lg file:px-4 file:py-2 file:text-[#0777be] file:font-bold hover:file:bg-blue-100 transition">
                    </div>
                </div>

                {{-- Status --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Status</label>
                    <label
                        class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm cursor-pointer">
                        <span class="text-sm font-bold text-gray-700">Active?</span>
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $subCategory->is_active) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#94c940] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full">
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('admin.sub-categories.index') }}"
                    class="px-4 py-2 text-xs font-bold text-gray-400 uppercase hover:text-gray-600">Cancel</a>
                <button type="submit"
                    class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg font-bold text-xs uppercase shadow-md hover:bg-[#0666a3] transition-all">
                    Update Sub-Category
                </button>
            </div>
        </form>
    </div>

    <script>
        function imagePreview() {
            return {
                // DIRECT ASSET PATH - NO STORAGE PREFIX
                imageUrl: '{{ $subCategory->image_path ? asset($subCategory->image_path) : '' }}',
                fileChosen(e) {
                    let f = e.target.files[0];
                    if (f) {
                        let r = new FileReader();
                        r.onload = (ex) => this.imageUrl = ex.target.result;
                        r.readAsDataURL(f);
                    }
                }
            }
        }
    </script>
@endsection
