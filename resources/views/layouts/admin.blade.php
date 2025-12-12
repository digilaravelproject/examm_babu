<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Exam Babu Admin') }}</title>

     <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/favicon.jpg') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Main Wrapper with Alpine for Mobile Sidebar Toggle (Outer Scope) -->
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">

        <!-- Sidebar -->
        <aside class="bg-gray-800 text-white min-h-screen flex-shrink-0 w-64 hidden md:block">
            <!-- Logo area -->
            <div class="flex items-center gap-3 p-4 border-b border-gray-700">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Exam Babu" class="h-10 w-25 object-contain rounded-sm ml-2">
            </div>

            <nav class="mt-4 px-2">
                {{-- Use Route::has() to avoid requiring new routes; fallback to '#' --}}
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">🏠</span> Home Dashboard
                </a>

                <a href="{{ Route::has('admin.file_manager.index') ? route('admin.file_manager.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.file_manager.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">📁</span> File Manager
                </a>

                <!-- ENGAGE section -->
                <div class="mt-6 px-4 text-xs text-teal-300 uppercase font-semibold">Engage</div>

                <a href="{{ Route::has('admin.tests.index') ? route('admin.tests.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.tests.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">📝</span> Manage Tests
                </a>

                <a href="{{ Route::has('admin.learning.index') ? route('admin.learning.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.learning.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">💡</span> Manage Learning
                </a>

                <!-- LIBRARY section -->
                <div class="mt-6 px-4 text-xs text-teal-300 uppercase font-semibold">Library</div>

                <a href="{{ Route::has('admin.question_bank.index') ? route('admin.question_bank.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.question_bank.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">📚</span> Question Bank
                </a>

                <a href="{{ Route::has('admin.lesson_bank.index') ? route('admin.lesson_bank.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.lesson_bank.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">📘</span> Lesson Bank
                </a>

                <a href="{{ Route::has('admin.video_bank.index') ? route('admin.video_bank.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.video_bank.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">🎥</span> Video Bank
                </a>

                <!-- CONFIGURATION section -->
                <div class="mt-6 px-4 text-xs text-teal-300 uppercase font-semibold">Configuration</div>

                <a href="{{ Route::has('admin.monetization.index') ? route('admin.monetization.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.monetization.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">💰</span> Monetization
                </a>

                <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">👥</span> Manage Users
                </a>

                <a href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">📂</span> Manage Categories
                </a>

                <a href="{{ Route::has('admin.subjects.index') ? route('admin.subjects.index') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.subjects.*') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">🧭</span> Manage Subjects
                </a>

                <a href="{{ Route::has('admin.settings') ? route('admin.settings') : '#' }}"
                   class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.settings') ? 'bg-gray-700' : '' }}">
                    <span class="mr-2">⚙️</span> Settings
                </a>

                <!-- keep the small admin utilities (roles/logs) present like original -->
                <div class="mt-6 border-t border-gray-700 pt-4 px-2">
                    <a href="{{ Route::has('admin.roles_permissions.index') ? route('admin.roles_permissions.index') : '#' }}"
                       class="block py-2.5 px-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.roles_permissions.*') ? 'bg-gray-700' : '' }}">
                        <span class="mr-2">🔑</span> Roles & Permissions
                    </a>
                    <a href="{{ Route::has('admin.logs') ? route('admin.logs') : '#' }}"
                       class="block py-2.5 px-4 rounded hover:bg-gray-700 mt-1 {{ request()->routeIs('admin.logs') ? 'bg-gray-700' : '' }}">
                        <span class="mr-2">📜</span> Activity Logs
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow p-4 flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{-- Header Section defined in child views --}}
                    @yield('header', 'Admin Dashboard')
                </h2>

                <!-- User Dropdown (Inner Scope) -->
                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                        <div>{{ Auth::user()->name }}</div>
                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen"
                         @click.away="dropdownOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                         style="display: none;">

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Out</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                {{-- Content Section defined in child views --}}
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
