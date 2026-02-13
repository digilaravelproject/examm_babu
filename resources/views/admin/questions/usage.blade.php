@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Question Usage Analysis')

@php
    // FIX: Dynamic Route Logic for Back Button
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';

    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }

    // Generate Dynamic Back URL
    $backUrl = route($routePrefix . 'questions.index', $routeParams);
@endphp

@section('content')
    <div class="py-6 mx-auto space-y-6 max-w-7xl">

        {{-- Header with Back Button --}}
        <div class="flex items-center justify-between px-4 sm:px-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Question Usage Analysis</h1>
                <p class="text-sm text-gray-500">See which exams currently contain this question.</p>
            </div>
            {{-- FIX: Updated href to use dynamic variable --}}
            <a href="{{ $backUrl }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                &larr; Back to Questions
            </a>
        </div>

        {{-- Question Details Card --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-1 font-mono text-xs font-bold text-blue-700 bg-blue-100 rounded">
                            {{ $question->code }}
                        </span>
                        <span class="text-xs font-bold tracking-wider text-gray-500 uppercase">
                            {{ $question->questionType->code ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded">
                            {{ $question->skill->name ?? 'No Subject' }} / {{ $question->topic->name ?? 'No Topic' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="prose-sm prose text-gray-800 max-w-none">
                    {!! $question->question !!}
                </div>
            </div>
        </div>

        {{-- Linked Exams List --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="font-bold text-gray-800">Linked Exams ({{ $question->exams->count() }})</h3>
            </div>

            @if ($question->exams->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Exam Title
                                </th>
                                <th class="px-6 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Duration</th>
                                <th class="px-6 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($question->exams as $exam)
                                <tr class="transition hover:bg-gray-50/80">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-[#0777be]">{{ $exam->title ?? $exam->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $exam->exam_category->name ?? 'General' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $exam->created_at->format('d M, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $exam->duration ?? 0 }} Mins
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($exam->is_active)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="p-3 mb-3 rounded-full bg-gray-50">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Not Used Yet</h3>
                    <p class="mt-1 text-xs text-gray-500">This question hasn't been added to any exam yet.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
