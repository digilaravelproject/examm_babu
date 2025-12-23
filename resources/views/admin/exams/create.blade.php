@extends('layouts.admin')

@section('content')
<div class="max-w-5xl py-6 mx-auto">
    @include('admin.exams.partials._steps', ['activeStep' => 'details'])

    <div class="mt-6">
        <h2 class="mb-4 text-xl font-bold text-gray-800">Create New Exam</h2>
        <form action="{{ route('admin.exams.store') }}" method="POST">
            @include('admin.exams.partials._form')
        </form>
    </div>
</div>
@endsection
