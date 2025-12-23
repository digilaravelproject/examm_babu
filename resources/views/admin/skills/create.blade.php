@extends('layouts.admin')

@section('title', 'Create Skill')

@section('content')
<div class="max-w-5xl py-6 mx-auto space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Skill</h1>
            <p class="text-sm text-gray-500">Define a specific skill and link it to a parent section.</p>
        </div>
        <a href="{{ route('admin.skills.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    {{-- Form Card --}}
    <div class="overflow-hidden font-sans bg-white border border-gray-200 shadow-lg rounded-xl">
        <form action="{{ route('admin.skills.store') }}" method="POST">
            @csrf

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur">
                <h3 class="flex items-center gap-2 text-base font-extrabold tracking-wide text-gray-800 uppercase">
                    <span class="text-xl">🎯</span>
                    Skill Configuration
                </h3>
            </div>

            @include('admin.skills.partials._form', ['skill' => new \App\Models\Skill(), 'sections' => $sections])

            {{-- Card Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <a href="{{ route('admin.skills.index') }}"
                   class="px-4 py-2 text-xs font-bold tracking-wide text-gray-500 uppercase transition hover:text-gray-800">
                    Discard
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg shadow-md hover:bg-[#0666a3] font-bold text-xs uppercase tracking-wide transform hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Save Skill
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
