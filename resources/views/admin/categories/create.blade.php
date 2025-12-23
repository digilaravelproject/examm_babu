@extends('layouts.admin')

@section('title', 'Create Category')

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Category</h1>
            <p class="text-sm text-gray-500">Add a new category for organizing your questions and exams.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    @include('admin.categories.partials._form', ['category' => new \App\Models\Category()])
</div>
@endsection
