@extends('layouts.admin')

@section('title', 'Create Question')

@section('content')
<div class="max-w-7xl mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Question</h1>
            <p class="text-sm text-gray-500">Type: <span class="text-[#0777be] font-semibold">{{ $questionType->name }}</span></p>
        </div>
        <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.questions.index' : 'admin.questions.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancel
        </a>
    </div>

    {{-- Pass a new Question instance --}}
    @include('admin.questions.partials._form', ['question' => new \App\Models\Question()])
</div>
@endsection
