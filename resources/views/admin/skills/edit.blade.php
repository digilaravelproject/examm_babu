@extends('layouts.admin')

@section('title', 'Edit Skill')

@section('content')
<div class="max-w-5xl py-6 mx-auto space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
                Edit Skill
                @if($skill->is_active)
                    <span class="px-2 py-0.5 text-[10px] bg-[#94c940] text-white rounded-full uppercase font-bold shadow-sm">Active</span>
                @else
                    <span class="px-2 py-0.5 text-[10px] bg-orange-500 text-white rounded-full uppercase font-bold shadow-sm">Inactive</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">
                Editing Skill: <span class="font-bold text-[#0777be]">{{ $skill->name }}</span>
                <span class="mx-2">|</span> Code: <span class="font-mono text-[#f062a4] font-bold">{{ $skill->code }}</span>
            </p>
        </div>
        <a href="{{ route('admin.skills.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Back to List
        </a>
    </div>

    {{-- Form Card --}}
    <div class="overflow-hidden font-sans bg-white border border-gray-200 shadow-lg rounded-xl">
        <form action="{{ route('admin.skills.update', $skill->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur">
                <h3 class="flex items-center gap-2 text-base font-extrabold tracking-wide text-gray-800 uppercase">
                    <span class="text-xl">✏️</span>
                    Update Skill Details
                </h3>
            </div>

            @include('admin.skills.partials._form', ['skill' => $skill, 'sections' => $sections])

            {{-- Card Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <a href="{{ route('admin.skills.index') }}"
                   class="px-4 py-2 text-xs font-bold tracking-wide text-gray-500 uppercase transition hover:text-gray-800">
                    Discard Changes
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg shadow-md hover:bg-[#0666a3] font-bold text-xs uppercase tracking-wide transform hover:-translate-y-0.5 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16m8-8H4"></path>
                    </svg>
                    Update Skill
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
