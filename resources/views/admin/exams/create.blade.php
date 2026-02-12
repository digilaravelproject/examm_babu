@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')
@section('title', 'Create New Exam')

@php
    // --- Dynamic Route Logic ---
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';

    // Prepare Parameters
    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }

    // Pre-generate URLs
    $urlIndex = route($routePrefix . 'exams.index', $routeParams);
    $urlStore = route($routePrefix . 'exams.store', $routeParams);
@endphp

@section('content')
<div class="max-w-5xl py-8 mx-auto">
    {{-- Steps Header (Ensure partial handles dynamic routes too or pass vars) --}}
    @include('admin.exams.partials._steps', ['activeStep' => 'details', 'routePrefix' => $routePrefix, 'routeParams' => $routeParams])

    <div class="mt-8">
        {{-- Header with Back Button --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Create New Exam</h2>
                <p class="text-sm text-gray-500">Fill in the basic details to start setting up your mock test.</p>
            </div>
            <a href="{{ $urlIndex }}" class="text-sm font-semibold transition-colors text-gray-400 hover:text-[var(--brand-blue)] flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to List
            </a>
        </div>

        {{-- Form Card --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <div class="p-1" style="background-color: var(--brand-blue);"></div> {{-- Top Accent Line --}}
            <div class="p-6 md:p-8">
                <form action="{{ $urlStore }}" method="POST">
                    @csrf
                    @include('admin.exams.partials._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
