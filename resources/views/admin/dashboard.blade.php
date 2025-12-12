{{-- 1. Layout ko Extend kiya --}}
@extends('layouts.admin')

{{-- 2. Header Section Define kiya --}}
@section('header', 'Admin Dashboard')

{{-- 3. Main Content Section --}}
@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Card 1: Total Users -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
            <p class="text-2xl font-bold">{{ $totalUsers }}</p>
        </div>

        <!-- Card 2: Active Roles -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Questions</h3>
            <p class="text-2xl font-bold">16318</p>
        </div>

        <!-- Placeholder Card (Optional) -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Quizzes</h3>
            <p class="text-2xl font-bold">0</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Card 1: Total Users -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Practice Sets</h3>
            <p class="text-2xl font-bold">0</p>
        </div>

        <!-- Card 2: Active Roles -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm font-medium">Active Roles</h3>
            <p class="text-2xl font-bold">{{ $totalRoles }}</p>
        </div>

        <!-- Placeholder Card (Optional) -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm font-medium">Pending Approvals</h3>
            <p class="text-2xl font-bold">0</p>
        </div>
    </div>

    <!-- Green Card -->
    <div class="bg-green-700 text-white p-6 rounded-lg shadow-md border border-green-800" style="background: green;">
        <h2 class="text-lg font-bold">To Teach is To Learn Twice Over.</h2>
        <p class="mt-2 text-sm opacity-90">
            To get most of your account, checkout our docs to understand how this app can serve you and your team.
        </p>

        <div class="text-right mt-4">
            <a href="https://nearchip.gitbook.io/qwiktest" 
            class="bg-white text-green-700 px-4 py-1 rounded shadow hover:bg-gray-100 text-sm font-semibold border border-gray-200">
                View Docs
            </a>
        </div>
    </div>

    <!-- Recent Activity Logs -->
    <?php /*<div class="bg-white shadow rounded-lg p-6 mt-6">
        <h3 class="text-lg font-bold mb-4">Recent Activity Logs</h3>
        <ul>
            @foreach($recentActivities as $activity)
                <li class="border-b py-2 text-sm">
                    <span class="font-bold text-blue-600">{{ $activity->causer->name ?? 'System' }}</span>
                    {{ $activity->description }}
                    <span class="text-gray-400 float-right">{{ $activity->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-4 text-right">
            <a href="{{ route('admin.logs') }}" class="text-blue-500 hover:underline">View All Logs &rarr;</a>
        </div>
    </div> */?>
@endsection
