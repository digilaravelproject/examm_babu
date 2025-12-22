@extends('layouts.admin')

@section('title', 'Edit Passage')

@section('content')
<div class="max-w-7xl mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                Edit Passage
                @if($passage->is_active)
                    <span class="px-2 py-0.5 text-xs bg-[#94c940] text-white rounded-full">Active</span>
                @else
                    <span class="px-2 py-0.5 text-xs bg-orange-500 text-white rounded-full">Inactive</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">Code: <span class="font-mono text-[#f062a4]">{{ $passage->code }}</span></p>
        </div>
        <a href="{{ route('admin.comprehensions.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition shadow-sm font-medium">
            Back
        </a>
    </div>

    @include('admin.comprehensions.partials._form', ['passage' => $passage])
</div>
@endsection
