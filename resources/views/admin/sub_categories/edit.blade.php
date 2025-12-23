@extends('layouts.admin')

@section('title', 'Edit Sub-Category')

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    {{-- Top Header Section --}}
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
                Edit Sub-Category
                @if($subCategory->is_active)
                    <span class="px-2 py-0.5 text-[10px] bg-[#94c940] text-white rounded-full uppercase tracking-wider font-bold shadow-sm">Active</span>
                @else
                    <span class="px-2 py-0.5 text-[10px] bg-orange-500 text-white rounded-full uppercase tracking-wider font-bold shadow-sm">Inactive</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">
                System Code: <span class="font-mono text-[#f062a4] font-bold">{{ $subCategory->code }}</span>
            </p>
        </div>
        <a href="{{ route('admin.sub-categories.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Back to List
        </a>
    </div>

    {{-- Form Inclusion --}}
    @include('admin.sub_categories.partials._form', [
        'subCategory' => $subCategory,
        'categories' => $categories,
        'types' => $types
    ])
</div>
@endsection
