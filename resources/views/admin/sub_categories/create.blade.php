@extends('layouts.admin')

@section('title', 'Create Sub-Category')
@section('header', 'Create Sub-Category')

@section('content')
    <div class="py-6 mx-auto space-y-6 max-w-7xl">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 sm:px-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create New Sub-Category</h1>
                <p class="text-sm text-gray-500">Add a new topic under a primary category.</p>
            </div>
            <a href="{{ route('admin.sub-categories.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                Back to List
            </a>
        </div>

        {{-- Form Container --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl" x-data="imagePreview()">

            {{-- Form Action: STORE (not Update) --}}
            <form action="{{ route('admin.sub-categories.store') }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf

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
                    {{-- Category --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Parent Category <span
                                class="text-red-500">*</span></label>
                        <select name="category_id" required
                            class="w-full text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                            <option value="">Select Parent</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Sub-Category Type <span
                                class="text-red-500">*</span></label>
                        <select name="sub_category_type_id" required
                            class="w-full text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                            <option value="">Select Type</option>
                            @foreach ($types as $t)
                                <option value="{{ $t->id }}"
                                    {{ old('sub_category_type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Name --}}
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="text-xs font-bold text-gray-600 uppercase">Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-[#0777be]"
                            placeholder="e.g., Algebra, organic Chemistry">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Image Upload --}}
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-600 uppercase">Icon/Image</label>
                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center w-20 h-20 overflow-hidden border-2 border-dashed rounded-lg bg-gray-50">
                                <template x-if="imageUrl">
                                    <img :src="imageUrl" class="object-cover w-full h-full">
                                </template>
                                <template x-if="!imageUrl">
                                    <span class="text-[10px] text-gray-400">Preview</span>
                                </template>
                            </div>

                            {{-- Input Name: image_path --}}
                            <div class="flex-1">
                                <input type="file" name="image_path" @change="fileChosen" accept="image/*"
                                    class="text-xs file:bg-blue-50 file:border-0 file:rounded-lg file:px-4 file:py-2 file:text-[#0777be] file:font-bold hover:file:bg-blue-100 transition block w-full text-gray-500">
                                <p class="mt-1 text-[10px] text-gray-400">Optional. Supported: jpg, png, webp.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-600 uppercase">Status <span
                                class="text-red-500">*</span></label>
                        <select name="is_active"
                            class="w-full text-sm border-gray-300 rounded-lg focus:border-[#0777be] focus:ring-[#0777be]">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                {{-- Short Description --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase">Short Description</label>
                    <textarea name="short_description" rows="3"
                        class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-[#0777be]">{{ old('short_description') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t bg-gray-50 -mx-6 -mb-6 px-6 py-4 mt-4">
                    <a href="{{ route('admin.sub-categories.index') }}"
                        class="px-4 py-2 text-xs font-bold text-gray-500 uppercase transition hover:text-gray-700">Cancel</a>
                    <button type="submit"
                        class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg font-bold text-xs uppercase shadow-md hover:bg-[#0666a3] transition-all">
                        Save Sub-Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function imagePreview() {
            return {
                imageUrl: null,
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
