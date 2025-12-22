@extends('layouts.admin')

@section('title', 'Create Passage')

@section('content')
<div class="max-w-7xl mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between px-4 sm:px-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Passage</h1>
            <p class="text-sm text-gray-500">Add a new reading passage for comprehension questions.</p>
        </div>
        <a href="{{ route('admin.comprehensions.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition shadow-sm font-medium">
            Cancel
        </a>
    </div>

    {{-- Pass a new instance --}}
    @include('admin.comprehensions.partials._form', ['passage' => new \App\Models\ComprehensionPassage()])
</div>
@endsection
