@extends('layouts.admin')

@section('title', 'Create Sub-Category')

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    {{-- Top Header Section --}}
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Sub-Category</h1>
            <p class="text-sm text-gray-500">Define a new sub-topic and link it to a parent category.</p>
        </div>
        <a href="{{ route('admin.sub-categories.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    {{-- Form Inclusion --}}
    @include('admin.sub_categories.partials._form', [
        'subCategory' => new \App\Models\SubCategory(),
        'categories' => $categories,
        'types' => $types
    ])
</div>
@endsection
