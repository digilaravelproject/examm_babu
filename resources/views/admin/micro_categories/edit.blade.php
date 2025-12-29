@extends('layouts.admin')
@section('title', 'Edit Micro Category')
@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <h1 class="text-2xl font-bold text-gray-900">Edit Micro Category</h1>
        <a href="{{ route('admin.micro-categories.index') }}" class="px-4 py-2 text-sm text-gray-700 border rounded-lg hover:bg-gray-50">Cancel</a>
    </div>
    @include('admin.micro_categories.partials._form', [
        'microCategory' => $microCategory,
        'subCategories' => $subCategories,
        'route' => route('admin.micro-categories.update', $microCategory->id),
        'method' => 'PUT'
    ])
</div>
@endsection
