@extends('layouts.admin')

@section('title', 'Edit Advertisement')
@section('header', 'Edit Advertisement')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.advertisements.index') }}" class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Advertisements
        </a>
    </div>

    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Edit Advertisement: {{ $advertisement->title }}</h2>
        </div>

        <form action="{{ route('admin.advertisements.update', $advertisement->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Title --}}
                <div class="space-y-2">
                    <label for="title" class="block text-sm font-semibold text-gray-700">Advertisement Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required value="{{ old('title', $advertisement->title) }}"
                        class="w-full px-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                        placeholder="e.g., Summer Special Offer">
                    @error('title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Location --}}
                <div class="space-y-2">
                    <label for="location" class="block text-sm font-semibold text-gray-700">Location <span class="text-red-500">*</span></label>
                    <select name="location" id="location" required
                        class="w-full px-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="report_banner" {{ old('location', $advertisement->location) == 'report_banner' ? 'selected' : '' }}>Report Banner (Top/Bottom)</option>
                        <option value="sidebar" {{ old('location', $advertisement->location) == 'sidebar' ? 'selected' : '' }}>Sidebar Ad</option>
                    </select>
                </div>

                {{-- Current Image Preview --}}
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700">Current Banner</label>
                    <div class="relative w-full max-w-lg h-32 border border-gray-200 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $advertisement->image_path) }}" alt="{{ $advertisement->title }}" class="object-contain h-full">
                    </div>
                </div>

                {{-- New Image Upload --}}
                <div class="space-y-2 md:col-span-2">
                    <label for="image" class="block text-sm font-semibold text-gray-700">Upload New Banner (Leave empty to keep existing)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="image" class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex flex-col items-center justify-center pt-2 pb-3">
                                <svg class="w-6 h-6 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            </div>
                            <input id="image" name="image" type="file" class="hidden" accept="image/*" />
                        </label>
                    </div>
                    @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Link URL --}}
                <div class="space-y-2 md:col-span-2">
                    <label for="link_url" class="block text-sm font-semibold text-gray-700">Target URL (Optional)</label>
                    <input type="url" name="link_url" id="link_url" value="{{ old('link_url', $advertisement->link_url) }}"
                        class="w-full px-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="https://example.com/promotion">
                </div>

                {{-- Status --}}
                <div class="flex items-center gap-3">
                    <div class="flex h-6 items-center">
                        <input id="status" name="status" type="checkbox" {{ $advertisement->status ? 'checked' : '' }} value="1"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    </div>
                    <div class="text-sm leading-6">
                        <label for="status" class="font-medium text-gray-900">Active</label>
                        <p class="text-gray-500 text-xs">If unchecked, this ad will not be displayed.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.advertisements.index') }}" class="px-6 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-8 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md">Update Advertisement</button>
            </div>
        </form>
    </div>
</div>
@endsection
