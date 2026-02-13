@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Passage Usage Details')
@section('header', 'Usage Details')

@php
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $routeParams = !$isAdmin ? ['role' => request()->route('role') ?? 'instructor'] : [];
@endphp

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Header / Back --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route($routePrefix . 'comprehensions.index', $routeParams) }}"
                   class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition shadow-sm">
                    &larr;
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Passage Usage</h1>
                    <p class="text-sm text-gray-500">Backtracking for: <span class="font-bold text-indigo-600">{{ $passage->title }}</span></p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-sm font-bold border border-blue-100">
                    Total Questions: {{ $passage->questions->count() }}
                </span>
            </div>
        </div>

        {{-- Passage Preview --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3">Passage Content</h3>
            <div class="prose max-w-none text-gray-600 text-sm bg-gray-50 p-4 rounded-lg border border-gray-100">
                {!! $passage->body !!}
            </div>
        </div>

        {{-- Questions List --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Linked Questions</h3>
            </div>

            @if($passage->questions->isEmpty())
                <div class="p-12 text-center text-gray-500">
                    No questions are linked to this passage yet.
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($passage->questions as $question)
                        <div class="p-6 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 font-bold text-xs rounded border border-gray-200">
                                        {{ $question->questionType->code ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex-1 space-y-3">
                                    {{-- Question Text --}}
                                    <div class="text-sm text-gray-800 font-medium">
                                        {!! $question->question !!}
                                    </div>

                                    {{-- Associated Exams --}}
                                    <div class="flex items-start gap-2 text-xs">
                                        <span class="font-bold text-gray-500 mt-0.5">Used in Exams:</span>
                                        <div class="flex flex-wrap gap-2">
                                            @forelse($question->exams as $exam)
                                                <span class="px-2 py-0.5 bg-green-50 text-green-700 border border-green-100 rounded-md">
                                                    {{ $exam->title }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 italic">Not added to any exam yet.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Link --}}
                                <div>
                                    <a href="{{ route($routePrefix . 'questions.edit', array_merge($routeParams, ['question' => $question->id])) }}"
                                       target="_blank"
                                       class="text-blue-600 hover:underline text-xs font-bold">
                                        Edit Question &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection
