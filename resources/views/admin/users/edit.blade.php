@extends('layouts.admin')

@section('title', 'Edit User')
@section('header', 'Edit User')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit User: {{ $user->first_name }}</h1>
            <p class="text-sm text-gray-500">Update user details and permissions.</p>
        </div>

        {{-- Back Button (Optional) --}}
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
            &larr; Back to Users
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin.users.partials._form')
            </form>
        </div>
    </div>
</div>
@endsection
