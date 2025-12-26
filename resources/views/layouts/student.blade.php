<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Exam Babu') }} - Student Portal</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/favicon.jpg') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
          :root {
            --brand-blue: #0777be;
            --brand-pink: #f062a4;
            --brand-green: #94c940;
            --brand-sky: #7fd2ea;
            --sidebar-bg: #0f172a;
        }
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar for Sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 5px;
        }

        .sidebar-scroll:hover::-webkit-scrollbar-thumb {
            background: #d1d5db;
        }
    </style>
</head>

<body class="h-full font-sans antialiased text-gray-900 bg-gray-50" x-data="{ sidebarOpen: false }">

    <!-- Main Container -->
    <div class="flex h-screen overflow-hidden bg-gray-50">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75 backdrop-blur-sm"
                @click="sidebarOpen = false"></div>

            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                class="relative flex flex-col flex-1 w-full max-w-xs bg-white shadow-xl">
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100">
                    <span class="text-xl font-extrabold tracking-tight text-blue-600">ExamBabu</span>
                    <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto sidebar-scroll">
                    <!-- Mobile Navigation Links -->
                    <nav class="px-2 py-4 space-y-1">
                        <a href="{{ route('student.dashboard') }}"
                            class="group flex items-center px-3 py-2 text-base font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('student.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                            <svg class="mr-4 flex-shrink-0 h-6 w-6 {{ request()->routeIs('student.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('student.exam_demo') }}"
                            class="flex items-center px-3 py-2 text-base font-medium text-gray-700 transition-colors duration-200 rounded-lg group hover:bg-blue-50 hover:text-blue-600">
                            <svg class="flex-shrink-0 w-6 h-6 mr-4 text-gray-400 group-hover:text-blue-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Take Demo Exam
                            <span
                                class="ml-auto inline-block py-0.5 px-2 text-xs font-bold text-green-700 bg-green-100 rounded-full shadow-sm">NEW</span>
                        </a>

                        <a href="#"
                            class="flex items-center px-3 py-2 text-base font-medium text-gray-700 transition-colors duration-200 rounded-lg group hover:bg-blue-50 hover:text-blue-600">
                            <svg class="flex-shrink-0 w-6 h-6 mr-4 text-gray-400 group-hover:text-blue-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Live Tests
                        </a>

                        <a href="#"
                            class="flex items-center px-3 py-2 text-base font-medium text-gray-700 transition-colors duration-200 rounded-lg group hover:bg-blue-50 hover:text-blue-600">
                            <svg class="flex-shrink-0 w-6 h-6 mr-4 text-gray-400 group-hover:text-blue-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            My Attempts
                        </a>
                    </nav>

                    <!-- Mobile User & Logout -->
                    <div class="pt-4 pb-3 border-t border-gray-100">
                        <div class="flex items-center px-4">
                            <div class="flex-shrink-0">
                                <img class="w-10 h-10 border-2 border-white rounded-full shadow-sm"
                                    src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=FFFFFF&background=3B82F6&font-size=0.33"
                                    alt="">
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="px-2 mt-3 space-y-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full px-3 py-2 text-base font-medium text-left text-red-600 transition-colors rounded-lg hover:bg-red-50">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Sidebar (Static on Left) -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col flex-1 min-h-0 bg-white border-r border-gray-200 shadow-sm">
                    <div class="flex items-center flex-shrink-0 h-16 px-6 border-b border-gray-100">
                        <span class="text-2xl font-extrabold tracking-tight text-blue-600">ExamBabu<span
                                class="text-gray-400">.</span></span>
                    </div>
                    <div class="flex flex-col flex-1 overflow-y-auto sidebar-scroll">
                        <nav class="flex-1 px-3 py-6 space-y-1.5">
                            <!-- Sidebar Links -->
                            <a href="{{ route('student.dashboard') }}"
                                class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('student.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                                <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('student.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-500' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Dashboard
                            </a>

                            <a href="{{ route('student.exam_demo') }}"
                                class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all duration-200">
                                <svg class="flex-shrink-0 w-5 h-5 mr-3 text-gray-400 group-hover:text-blue-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Take Demo Exam
                                <span
                                    class="ml-auto inline-block py-0.5 px-2 text-[10px] font-bold tracking-wide text-green-700 bg-green-100 rounded-full shadow-sm">NEW</span>
                            </a>

                            <a href="#"
                                class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all duration-200">
                                <svg class="flex-shrink-0 w-5 h-5 mr-3 text-gray-400 group-hover:text-blue-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Live Tests
                                <span class="relative flex w-2 h-2 ml-auto">
                                    <span
                                        class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex w-2 h-2 bg-red-500 rounded-full"></span>
                                </span>
                            </a>

                            <a href="#"
                                class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-600 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all duration-200">
                                <svg class="flex-shrink-0 w-5 h-5 mr-3 text-gray-400 group-hover:text-blue-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                My Attempts
                            </a>
                        </nav>

                        <!-- Free Space Filler -->
                        <div class="flex-1"></div>

                        <!-- Upgrade/Promo Box (Optional for attractive look) -->
                        <div
                            class="p-4 m-3 text-white shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl">
                            <p class="text-xs font-bold tracking-wider uppercase opacity-80">Pro Plan</p>
                            <h4 class="mt-1 text-sm font-bold">Unlock All Tests</h4>
                            <p class="mt-1 text-xs text-blue-100 opacity-90">Get unlimited access to mocks.</p>
                            <button
                                class="mt-3 w-full py-1.5 bg-white text-blue-600 text-xs font-bold rounded shadow-sm hover:bg-gray-50 transition">Upgrade</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Column -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            <!-- Top Header with Search & Profile Dropdown -->
            <header
                class="sticky top-0 z-20 flex items-center justify-between h-16 px-4 border-b border-gray-200 shadow-sm bg-white/80 backdrop-blur-md sm:px-6 lg:px-8">

                <!-- Mobile Toggle -->
                <button @click="sidebarOpen = true"
                    class="text-gray-500 md:hidden focus:outline-none hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Search Bar -->
                <div class="flex justify-center flex-1 px-4 lg:justify-start lg:ml-4">
                    <div class="relative w-full max-w-lg lg:max-w-xs">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search"
                            class="block w-full py-2 pl-10 pr-3 leading-5 placeholder-gray-500 transition duration-150 ease-in-out border border-gray-300 rounded-full bg-gray-50 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Search for tests, exams...">
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">

                    <!-- Notification Bell -->
                    <button class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors relative">
                        <span
                            class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                    </button>

                    <!-- Profile Dropdown (Alpine.js) -->
                    <div class="relative" x-data="{ dropdownOpen: false }">
                        <button @click="dropdownOpen = !dropdownOpen"
                            class="flex items-center space-x-2 focus:outline-none">
                            <img class="object-cover transition-all border border-gray-200 rounded-full w-9 h-9 ring-2 ring-transparent hover:ring-blue-100"
                                src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=FFFFFF&background=3B82F6&font-size=0.33"
                                alt="">
                            <div class="hidden text-left md:block">
                                <p class="text-sm font-semibold leading-none text-gray-700">
                                    {{ explode(' ', Auth::user()->name)[0] }}</p>
                                <p class="text-[10px] text-gray-500 font-medium">Student</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-50 w-48 py-1 mt-2 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
                            style="display: none;">

                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>

                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-blue-600">
                                Edit Profile
                            </a>
                            <a href="#"
                                class="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-blue-600">
                                Account Settings
                            </a>

                            <div class="border-t border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full px-4 py-2 text-sm text-left text-red-600 transition-colors hover:bg-red-50">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Scrollable Content Area -->
            <main class="relative flex-1 overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
