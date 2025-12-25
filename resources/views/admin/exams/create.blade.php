@extends('layouts.admin')
@section('title', 'Create New Exam')

@section('content')
<div class="max-w-5xl py-8 mx-auto">
    {{-- Steps Header --}}
    @include('admin.exams.partials._steps', ['activeStep' => 'details'])

    <div class="mt-8">
        {{-- Header with Back Button --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Create New Exam</h2>
                <p class="text-sm text-gray-500">Fill in the basic details to start setting up your mock test.</p>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="text-sm font-semibold transition-colors text-gray-400 hover:text-[var(--brand-blue)] flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to List
            </a>
        </div>

        {{-- Form Card --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="p-1" style="background-color: var(--brand-blue);"></div> {{-- Top Accent Line --}}
            <div class="p-6 md:p-8">
                <form action="{{ route('admin.exams.store') }}" method="POST">
                    @csrf
                    @include('admin.exams.partials._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
