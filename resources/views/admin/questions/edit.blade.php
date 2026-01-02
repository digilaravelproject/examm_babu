@extends('layouts.admin')

@section('title', 'Edit Question')

@section('content')
    <div class="py-6 mx-auto space-y-6 max-w-7xl">
        <div class="flex items-center justify-between px-4 sm:px-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Question</h1>
                <p class="text-sm text-gray-500">Code: {{ $question->code }}</p>
            </div>
            <a href="{{ route('admin.questions.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
        </div>

        {{-- Load the Form Partial with Existing Question --}}
        @include('admin.questions.partials._form', ['question' => $question])
    </div>
@endsection
