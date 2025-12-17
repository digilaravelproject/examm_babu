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
    <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

        <!-- Sidebar -->
        <aside class="flex-shrink-0 hidden w-64 min-h-screen text-white bg-gray-800 md:block">
            <!-- Logo area -->
            <div class="flex items-center gap-3 p-4 border-b border-gray-700">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Exam Babu" class="object-contain h-10 ml-2 rounded-sm w-25">
            </div>

            <nav class="px-2 mt-4">
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
                <div class="px-4 mt-6 text-xs font-semibold text-teal-300 uppercase">Engage</div>

                <!-- Manage Tests dropdown (keeps design & icons) -->
                <div x-data="{ open: {{ request()->routeIs('admin.tests.*','admin.quizzes.*','admin.exams.*','admin.quiztypes.*','admin.examtypes.*') ? 'true' : 'false' }} }" class="relative">
                    <button
                        @click="open = !open"
                        type="button"
                        class="w-full flex items-center justify-between py-2.5 px-4 rounded hover:bg-gray-700 mt-1
                            {{ request()->routeIs('admin.tests.*','admin.quizzes.*','admin.exams.*','admin.quiztypes.*','admin.examtypes.*') ? 'bg-gray-700' : '' }}">
                        <span class="mr-2">📝</span>
                        <span class="flex-1 text-left">Manage Tests</span>

                        <!-- small chevron to indicate dropdown - matches neutral look -->
                        <svg :class="open ? 'transform rotate-180' : ''" class="w-4 h-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Submenu: keep same visual style as other menu items, slightly indented -->
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mt-1" style="margin-left: 20%;">

                        <a href="{{ Route::has('admin.quizzes.index') ? route('admin.quizzes.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.quizzes.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">📚</span> Quizzes
                        </a>

                        <a href="{{ Route::has('admin.exam.index') ? route('admin.exam.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.exam.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">🎓</span> Exams
                        </a>

                        <a href="{{ Route::has('admin.quiz-types.index') ? route('admin.quiz-types.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.quiz-types.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">⚙️</span> Quiz Types
                        </a>

                        <a href="{{ Route::has('admin.exam-types.index') ? route('admin.exam-types.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.exam-types.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">🧭</span> Exam Types
                        </a>
                    </div>
                </div>

                {{-- Manage Learning sidebar block (copy/paste alongside your existing Manage Tests block) --}}
                <div x-data="{ open: {{ request()->routeIs(
                        'admin.practice-sets.*',
                        'admin.lessons.*',
                        'admin.videos.*'
                    ) ? 'true' : 'false' }} }" class="relative">
                    <button
                        @click="open = !open"
                        type="button"
                        class="w-full flex items-center justify-between py-2.5 px-4 rounded hover:bg-gray-700 mt-1
                            {{ request()->routeIs('admin.practice-sets.*','admin.lessons.*','admin.videos.*') ? 'bg-gray-700' : '' }}">
                        <span class="mr-2">💡</span>
                        <span class="flex-1 text-left">Manage Learning</span>

                        <!-- chevron -->
                        <svg :class="open ? 'transform rotate-180' : ''" class="w-4 h-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mt-1"
                        style="margin-left: 20%;">

                        <a href="{{ Route::has('admin.practice-sets.index') ? route('admin.practice-sets.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.practice-sets.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">🧩</span> Practice Sets
                        </a>

                        <a href="{{ Route::has('admin.lessons.index') ? route('admin.lessons.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.lessons.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">📘</span> Lessons
                        </a>

                        <a href="{{ Route::has('admin.videos.index') ? route('admin.videos.index') : '#' }}"
                        class="block py-2.5 pl-8 pr-4 rounded hover:bg-gray-700 {{ request()->routeIs('admin.videos.*') ? 'bg-gray-700' : '' }}">
                            <span class="mr-2">🎬</span> Videos
                        </a>
                    </div>
                </div>

                <!-- LIBRARY section -->
                <div class="px-4 mt-6 text-xs font-semibold text-teal-300 uppercase">Library</div>

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
                <div class="px-4 mt-6 text-xs font-semibold text-teal-300 uppercase">Configuration</div>

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
                <div class="px-2 pt-4 mt-6 border-t border-gray-700">
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
        <div class="flex flex-col flex-1">
            <!-- Header -->
            <header class="flex items-center justify-between p-4 bg-white shadow">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{-- Header Section defined in child views --}}
                    @yield('header', 'Admin Dashboard')
                </h2>

                <!-- User Dropdown (Inner Scope) -->
                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                        <div>{{ Auth::user()->name }}</div>
                        <svg class="w-4 h-4 ml-1 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
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
                         class="absolute right-0 z-50 w-48 py-1 mt-2 bg-white rounded-md shadow-lg"
                         style="display: none;">

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">Log Out</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
</body>
</html>
