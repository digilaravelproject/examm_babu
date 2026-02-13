@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Create Subject')

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
            <h1 class="text-2xl font-bold text-gray-900">Add New Subject</h1>
            <p class="text-sm text-gray-500">Define a subject and link it to a micro category.</p>
        </div>
        <a href="{{ route($routePrefix . 'skills.index', $params) }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    @include('admin.skills.partials._form', [
        'skill' => new \App\Models\Skill(),
        'microCategories' => $microCategories,
        'action' => route($routePrefix . 'skills.store', $params),
        'method' => 'POST'
    ])
</div>
@endsection
