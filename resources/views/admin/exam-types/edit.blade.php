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
    $urlIndex  = route($routePrefix . 'exam-types.index', $routeParams);

    // Merge Exam Type ID for update
    $updateParams = array_merge($routeParams, ['exam_type' => $examType->id]);
    $urlUpdate = route($routePrefix . 'exam-types.update', $updateParams);
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
                <h2 class="text-xl font-extrabold text-gray-900">Edit Exam Type</h2>
                <p class="mt-1 text-sm text-gray-500">Update details for <span
                        class="px-1 font-mono text-xs bg-gray-200 rounded">{{ $examType->code }}</span></p>
            </div>

            {{-- FIX: Dynamic Update Route --}}
            <form action="{{ $urlUpdate }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                {{-- Name Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold tracking-wider text-gray-500 uppercase">Type Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $examType->name) }}"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)] transition-all bg-gray-50/30">
                    @error('name')
                        <span class="text-xs font-medium text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Code Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold tracking-wider text-gray-500 uppercase">Code <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $examType->code) }}"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)] transition-all bg-gray-50/30 font-mono uppercase">
                    @error('code')
                        <span class="text-xs font-medium text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Status Field --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold tracking-wider text-gray-500 uppercase">Status</label>
                    <div class="flex gap-4">
                        <label
                            class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 w-full {{ old('is_active', $examType->is_active) == '1' ? 'bg-blue-50 border-blue-200' : '' }}">
                            <input type="radio" name="is_active" value="1"
                                {{ old('is_active', $examType->is_active) == '1' ? 'checked' : '' }}
                                class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <span class="text-sm font-bold text-gray-700">Active</span>
                        </label>
                        <label
                            class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50 w-full {{ old('is_active', $examType->is_active) == '0' ? 'bg-blue-50 border-blue-200' : '' }}">
                            <input type="radio" name="is_active" value="0"
                                {{ old('is_active', $examType->is_active) == '0' ? 'checked' : '' }}
                                class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <span class="text-sm font-bold text-gray-700">Inactive</span>
                        </label>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex gap-4 pt-4">
                    <button type="submit" style="background-color: var(--brand-blue);"
                        class="flex-1 py-3.5 rounded-xl text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        Update Type
                    </button>
                    {{-- FIX: Dynamic Cancel Link --}}
                    <a href="{{ $urlIndex }}"
                        class="px-6 py-3.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
