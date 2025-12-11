<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Exam Babu Admin') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Main Wrapper with Alpine for Mobile Sidebar Toggle (Outer Scope) -->
    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">

        <!-- Sidebar -->
        <aside class="bg-gray-800 text-white min-h-screen flex-shrink-0 w-64 hidden md:block">
            <div class="p-4 text-xl font-bold border-b border-gray-700">Exam Babu Admin</div>
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="block py-2.5 px-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('admin.roles_permissions.index') }}" class="block py-2.5 px-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.roles_permissions.*') ? 'bg-gray-700' : '' }}">
                    🔑 Roles & Permissions
                </a>
                <a href="{{ route('admin.logs') }}" class="block py-2.5 px-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.logs') ? 'bg-gray-700' : '' }}">
                    📜 Activity Logs
                </a>
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
