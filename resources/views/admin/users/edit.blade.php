@extends('layouts.admin')

@section('title', 'Edit User')
@section('header', 'Edit User')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit User: <span class="text-[#0777be]">{{ $user->first_name }}</span></h1>
            <p class="text-sm text-gray-500">Update user details and permissions.</p>
        </div>

        {{-- Back Button --}}
        <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:text-[#0777be]">
            &larr; Back to Users
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
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
