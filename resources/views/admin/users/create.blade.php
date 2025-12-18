@extends('layouts.admin')

@section('title', 'Add New User')
@section('header', 'Add New User')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Add New User</h1>
        <p class="text-sm text-gray-500">Create a new user, assign roles and groups.</p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                @include('admin.users.partials._form')
            </form>
        </div>
    </div>
</div>
@endsection
