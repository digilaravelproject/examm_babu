@extends('layouts.admin')

@section('header', 'Create Category')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0777be] focus:ring-[#0777be]" placeholder="e.g., Mathematics">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0777be] focus:ring-[#0777be]" placeholder="e.g., MATH-001">
                    @error('code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                <textarea name="short_description" rows="2" class="w-full rounded-lg border-gray-300 focus:border-[#0777be] focus:ring-[#0777be]">{{ old('short_description') }}</textarea>
                @error('short_description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 focus:border-[#0777be] focus:ring-[#0777be]">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Image</label>
                    <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-[#0777be] hover:file:bg-blue-100">
                    @error('image') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="is_active" class="w-full rounded-lg border-gray-300 focus:border-[#0777be] focus:ring-[#0777be]">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-[#0777be] rounded-lg hover:bg-[#066099] shadow-sm">Create Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
