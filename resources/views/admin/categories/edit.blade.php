@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
                Edit Category
                @if($category->is_active)
                    <span class="px-2 py-0.5 text-[10px] bg-[#94c940] text-white rounded-full uppercase tracking-wider">Active</span>
                @else
                    <span class="px-2 py-0.5 text-[10px] bg-orange-500 text-white rounded-full uppercase tracking-wider">Inactive</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">Editing: <span class="font-mono text-[#f062a4] font-bold">{{ $category->name }}</span></p>
        </div>
        <a href="{{ route('admin.categories.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Back to List
        </a>
    </div>

    @include('admin.categories.partials._form', ['category' => $category])
</div>
@endsection
