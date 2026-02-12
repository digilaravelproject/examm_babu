@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Create Micro Category')

@php
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);
    $params = (!$isAdmin && $currentRole) ? ['role' => $currentRole] : [];
@endphp

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Micro Category</h1>
            <p class="text-sm text-gray-500">Add a specialized topic under a sub-category.</p>
        </div>
        <a href="{{ route($routePrefix . 'micro-categories.index', $params) }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    @include('admin.micro_categories.partials._form', [
        'microCategory' => new \App\Models\MicroCategory(),
        'subCategories' => $subCategories,
        'action' => route($routePrefix . 'micro-categories.store', $params),
        'method' => 'POST'
    ])
</div>
@endsection
