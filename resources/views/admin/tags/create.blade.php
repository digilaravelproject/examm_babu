@extends('layouts.admin')

@section('title', 'Create Tag')

@section('content')
<div class="max-w-4xl py-6 mx-auto space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Tag</h1>
            <p class="text-sm text-gray-500">Create a new keyword to organize questions and content.</p>
        </div>
        <a href="{{ route('admin.tags.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    {{-- Form Card --}}
    <div class="overflow-hidden font-sans bg-white border border-gray-200 shadow-lg rounded-xl">
        <form action="{{ route('admin.tags.store') }}" method="POST">
            @csrf

            {{-- Card Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur">
                <h3 class="flex items-center gap-2 text-base font-extrabold tracking-wide text-gray-800 uppercase">
                    <span class="text-xl">🏷️</span>
                    Tag Details
                </h3>
            </div>

            <div class="p-6 space-y-6">
                {{-- Tag Name Input --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">
                        Tag Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 font-bold">#</span>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border-gray-300 rounded-lg pl-8 pr-4 py-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium"
                               placeholder="e.g., Important, Geometry, 2024_Exams">
                    </div>
                    @error('name')
                        <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Toggle --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Publishing Status</label>
                    <label class="flex items-center justify-between p-3 transition bg-white border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-gray-50">
                        <span class="text-sm font-bold text-gray-700">Set as Active</span>
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', 1) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#94c940]"></div>
                        </div>
                    </label>
                    <p class="text-[10px] text-gray-400 mt-1 italic">Inactive tags won't appear in filters for students.</p>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <a href="{{ route('admin.tags.index') }}"
                   class="px-4 py-2 text-xs font-bold tracking-wide text-gray-500 uppercase transition hover:text-gray-800">
                    Discard
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg shadow-md hover:bg-[#0666a3] font-bold text-xs uppercase tracking-wide transform hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Tag
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
