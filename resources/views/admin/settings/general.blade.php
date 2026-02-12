@extends('layouts.admin')
@section('title', 'General Settings')
@section('content')

    @include('admin.settings._nav')

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- Site Information Card --}}
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Site Details</h2>
            <form action="{{ route('admin.settings.update-site') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">App Name</label>
                        <input type="text" name="app_name" value="{{ $settings->app_name }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tag Line</label>
                        <input type="text" name="tag_line" value="{{ $settings->tag_line }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">SEO Description</label>
                        <textarea name="seo_description" rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">{{ $settings->seo_description }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="can_register" id="can_register" value="1"
                            {{ $settings->can_register ? 'checked' : '' }}
                            class="rounded border-gray-300 text-[#0777be] shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                        <label for="can_register" class="ml-2 text-sm text-gray-600">Enable User Registration</label>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="bg-[#0777be] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#0777be]/90 transition">
                        Save Details
                    </button>
                </div>
            </form>
        </div>

        {{-- Logos Card --}}
        <div class="space-y-6">
            {{-- Main Logo --}}
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Site Logo</h2>
                <form action="{{ route('admin.settings.update-logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center gap-4">
                        @if ($settings->logo_path)
                            <div class="flex items-center justify-center w-16 h-16 border rounded-lg bg-gray-50">
                                <img src="{{ Storage::url($settings->logo_path) }}" class="max-w-full max-h-12">
                            </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="logo_path"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#0777be]/10 file:text-[#0777be] hover:file:bg-[#0777be]/20" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white transition bg-gray-800 rounded-lg hover:bg-gray-700">Update
                            Logo</button>
                    </div>
                </form>
            </div>

            {{-- Favicon --}}
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Favicon</h2>
                <form action="{{ route('admin.settings.update-favicon') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center gap-4">
                        @if ($settings->favicon_path)
                            <div class="flex items-center justify-center w-10 h-10 border rounded-lg bg-gray-50">
                                <img src="{{ Storage::url($settings->favicon_path) }}" class="max-w-full max-h-8">
                            </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="favicon_path"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#0777be]/10 file:text-[#0777be] hover:file:bg-[#0777be]/20" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white transition bg-gray-800 rounded-lg hover:bg-gray-700">Update
                            Favicon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
