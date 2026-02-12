@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Edit Passage')

@php
    // --- 1. Dynamic Route Logic ---
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';

    // Prepare Parameters
    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }

    // URL for Back Button
    $urlIndex = route($routePrefix . 'comprehensions.index', $routeParams);
@endphp

@section('content')
<div class="py-6 mx-auto space-y-6 max-w-7xl">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
                Edit Passage
                @if($passage->is_active)
                    <span class="px-2 py-0.5 text-xs bg-[#94c940] text-white rounded-full">Active</span>
                @else
                    <span class="px-2 py-0.5 text-xs bg-orange-500 text-white rounded-full">Inactive</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">Code: <span class="font-mono text-[#f062a4]">{{ $passage->code }}</span></p>
        </div>
        {{-- Fixed: Dynamic Link --}}
        <a href="{{ $urlIndex }}"
           class="px-4 py-2 font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
            Back
        </a>
    </div>

    {{-- Pass variables to the partial --}}
    @include('admin.comprehensions.partials._form', [
        'passage' => $passage,
        'routePrefix' => $routePrefix,
        'routeParams' => $routeParams
    ])
</div>
@endsection
