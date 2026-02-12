@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Create Section')

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
            <h1 class="text-2xl font-bold text-gray-900">Create New Section</h1>
            <p class="text-sm text-gray-500">Add a new section (e.g., Mathematics, Logic).</p>
        </div>
        <a href="{{ route($routePrefix . 'sections.index', $params) }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    @include('admin.sections.partials._form', [
        'section' => new \App\Models\Section(),
        'action' => route($routePrefix . 'sections.store', $params),
        'method' => 'POST'
    ])
</div>
@endsection
