@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Create Passage')

@php
    // --- 1. Dynamic Route Logic ---
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';

    // Prepare Parameters (for instructor role)
    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }

    // URL for Cancel Button
    $urlIndex = route($routePrefix . 'comprehensions.index', $routeParams);
@endphp

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Passage</h1>
            <p class="text-sm text-gray-500">Add a new reading passage for comprehension questions.</p>
        </div>
        {{-- Fixed: Dynamic Link --}}
        <a href="{{ $urlIndex }}"
           class="px-4 py-2 font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Cancel
        </a>
    </div>

    {{-- Pass variables to the partial --}}
    @include('admin.comprehensions.partials._form', [
        'passage' => new \App\Models\ComprehensionPassage(),
        'routePrefix' => $routePrefix,
        'routeParams' => $routeParams
    ])
</div>
@endsection
