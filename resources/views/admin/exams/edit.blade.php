@extends('layouts.admin')

@section('content')
<div class="max-w-5xl py-6 mx-auto">
    {{-- Steps Header --}}
    @include('admin.exams.partials._steps', ['activeStep' => 'details'])

    <div class="mt-6">
        <h2 class="mb-4 text-xl font-bold text-gray-800">Edit Exam Details</h2>
        <form action="{{ route('admin.exams.update', $exam->id) }}" method="POST">
            @include('admin.exams.partials._form')
        </form>
    </div>
</div>
@endsection
