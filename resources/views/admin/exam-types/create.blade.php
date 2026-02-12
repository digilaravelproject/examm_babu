@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('content')

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
    $urlIndex = route($routePrefix . 'exam-types.index', $routeParams);
    $urlStore = route($routePrefix . 'exam-types.store', $routeParams);
@endphp

    <div class="max-w-2xl px-4 py-8 mx-auto sm:px-6 lg:px-8">

        {{-- Back Link --}}
        {{-- FIX: Dynamic Back URL --}}
        <a href="{{ $urlIndex }}"
            class="inline-flex items-center gap-2 mb-6 text-sm text-gray-500 transition-colors hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Exam Types
        </a>

        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-xl font-extrabold text-gray-900">Create New Exam Type</h2>
                <p class="mt-1 text-sm text-gray-500">Define a new category for your exams</p>
            </div>

            {{-- FIX: Dynamic Store Action --}}
            <form action="{{ $urlStore }}" method="POST" class="p-8 space-y-6">
                @csrf

                {{-- Name Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold tracking-wider text-gray-500 uppercase">Type Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Mock Test"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)] transition-all bg-gray-50/30">
                    @error('name')
                        <span class="text-xs font-medium text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Code Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold tracking-wider text-gray-500 uppercase">Code <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. MOCK-TEST"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)] transition-all bg-gray-50/30 font-mono uppercase">
                    <p class="text-[10px] text-gray-400">Must be unique. Used for system identification.</p>
                    @error('code')
                        <span class="text-xs font-medium text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Status Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold tracking-wider text-gray-500 uppercase">Status</label>
                    <div class="flex gap-4">
                        <label
                            class="flex items-center w-full gap-2 p-3 border border-gray-200 cursor-pointer rounded-xl hover:bg-gray-50">
                            <input type="radio" name="is_active" value="1"
                                {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <span class="text-sm font-bold text-gray-700">Active</span>
                        </label>
                        <label
                            class="flex items-center w-full gap-2 p-3 border border-gray-200 cursor-pointer rounded-xl hover:bg-gray-50">
                            <input type="radio" name="is_active" value="0"
                                {{ old('is_active') == '0' ? 'checked' : '' }}
                                class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <span class="text-sm font-bold text-gray-700">Inactive</span>
                        </label>
                    </div>
                    @error('is_active')
                        <span class="text-xs font-medium text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button type="submit" style="background-color: var(--brand-blue);"
                        class="w-full py-3.5 rounded-xl text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        Create Exam Type
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
