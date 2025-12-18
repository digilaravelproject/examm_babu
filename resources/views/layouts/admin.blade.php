<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Exam Babu') }}</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/favicon.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- AlpineJS & SweetAlert --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }
        /* Smooth Scrollbar for Sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: #1e293b; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50 text-slate-600">

    {{-- Main App Wrapper --}}
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, fileManagerOpen: false }">

        {{-- ========================
             SIDEBAR (Modern Dark)
             ======================== --}}
        <aside class="fixed z-30 flex-shrink-0 w-64 h-full overflow-y-auto transition-transform transform border-r bg-slate-900 border-slate-800 md:translate-x-0 md:static sidebar-scroll"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo Area --}}
            <div class="flex items-center justify-center h-16 border-b shadow-sm bg-slate-950 border-slate-800">
                <div class="flex items-center gap-2 text-xl font-bold tracking-wider text-white">
                    {{-- Logo Image --}}
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-auto h-8 rounded-md">
                    {{-- <span>EXAM<span class="text-indigo-500">BABU</span></span> --}}
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="px-4 pb-4 mt-5 space-y-1">

                {{-- Dashboard --}}
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors group
                   {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                {{-- File Manager (Opens Popup) --}}
                <a href="#" @click.prevent="fileManagerOpen = true"
                   class="flex items-center px-4 py-3 mt-1 text-sm font-medium transition-colors rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white group">
                    <svg class="w-5 h-5 mr-3 text-slate-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    File Manager
                </a>

                {{-- SECTION LABEL --}}
                <div class="pt-5 pb-2">
                    <p class="px-4 text-xs font-semibold tracking-wider uppercase text-slate-500">Engagement</p>
                </div>

                {{-- Manage Tests Dropdown --}}
                <div x-data="{ open: {{ request()->routeIs('admin.tests.*', 'admin.quizzes.*', 'admin.exams.*', 'admin.quiztypes.*', 'admin.examtypes.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors text-slate-400 hover:bg-slate-800 hover:text-white
                            {{ request()->routeIs('admin.tests.*', 'admin.quizzes.*', 'admin.exams.*', 'admin.quiztypes.*', 'admin.examtypes.*') ? 'bg-slate-800 text-white' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Manage Tests
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mt-1 space-y-1 pl-11">
                        <a href="{{ Route::has('admin.quizzes.index') ? route('admin.quizzes.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Quizzes</a>
                        <a href="{{ Route::has('admin.exam.index') ? route('admin.exam.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Exams</a>
                        <a href="{{ Route::has('admin.quiz-types.index') ? route('admin.quiz-types.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Quiz Types</a>
                        <a href="{{ Route::has('admin.exam-types.index') ? route('admin.exam-types.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Exam Types</a>
                    </div>
                </div>

                {{-- Manage Learning Dropdown --}}
                <div x-data="{ open: {{ request()->routeIs('admin.practice-sets.*', 'admin.lessons.*', 'admin.videos.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors text-slate-400 hover:bg-slate-800 hover:text-white
                            {{ request()->routeIs('admin.practice-sets.*', 'admin.lessons.*', 'admin.videos.*') ? 'bg-slate-800 text-white' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Manage Learning
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mt-1 space-y-1 pl-11">
                        <a href="{{ Route::has('admin.practice-sets.index') ? route('admin.practice-sets.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Practice Sets</a>
                        <a href="{{ Route::has('admin.lessons.index') ? route('admin.lessons.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Lessons</a>
                        <a href="{{ Route::has('admin.videos.index') ? route('admin.videos.index') : '#' }}" class="block py-2 text-sm transition-transform text-slate-400 hover:text-white hover:translate-x-1">Videos</a>
                    </div>
                </div>

                {{-- SECTION LABEL --}}
                <div class="pt-5 pb-2">
                    <p class="px-4 text-xs font-semibold tracking-wider uppercase text-slate-500">Library</p>
                </div>

                <a href="{{ Route::has('admin.question_bank.index') ? route('admin.question_bank.index') : '#' }}" class="flex items-center px-4 py-2 text-sm font-medium transition-colors rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Question Bank
                </a>

                {{-- Add other library links similarly --}}

                {{-- SECTION LABEL --}}
                <div class="pt-5 pb-2">
                    <p class="px-4 text-xs font-semibold tracking-wider uppercase text-slate-500">System</p>
                </div>

                <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : '' }}">
                    <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Manage Users
                </a>

                <a href="{{ Route::has('admin.settings') ? route('admin.settings') : '#' }}" class="flex items-center px-4 py-2 text-sm font-medium transition-colors rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white">
                    <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Settings
                </a>

            </nav>
        </aside>

        {{-- Mobile Sidebar Backdrop --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm md:hidden"></div>

        {{-- ========================
             MAIN CONTENT WRAPPER
             ======================== --}}
        <div class="flex flex-col flex-1 h-full overflow-hidden bg-gray-50">

            {{-- Top Header --}}
            <header class="z-10 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 shadow-sm">

                {{-- Left: Mobile Toggle & Page Title --}}
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none md:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h2 class="text-xl font-bold text-gray-800">
                        @yield('header', 'Admin Panel')
                    </h2>
                </div>

                {{-- Right: User Profile --}}
                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-3 focus:outline-none group">

                        {{-- Name (Hidden on small screens) --}}
                        <div class="hidden text-right md:block">
                            <p class="text-sm font-semibold text-gray-700 group-hover:text-indigo-600">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 uppercase">{{ Auth::user()->roles->first()->name ?? 'Admin' }}</p>
                        </div>

                        {{-- Avatar (Initials or Image) --}}
                        <div class="relative">
                            @if(Auth::user()->profile_photo_url)
                                <img class="object-cover w-10 h-10 border-2 border-gray-100 rounded-full shadow-sm group-hover:border-indigo-100" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                            @else
                                <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white uppercase bg-indigo-600 border-2 border-gray-100 rounded-full shadow-sm">
                                    {{ substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                            {{-- Active Dot --}}
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>

                        {{-- Dropdown Arrow --}}
                        <svg :class="dropdownOpen ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-50 w-48 mt-2 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2 border-b border-gray-100 md:hidden">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Content Area --}}
            <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto bg-gray-50">
                @yield('content')
            </main>
        </div>

        {{-- ==========================================
             FILE MANAGER MODAL (Global Popup)
             ========================================== --}}
        <div x-show="fileManagerOpen"
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="fileManagerOpen"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm"
                 @click="fileManagerOpen = false"></div>

            {{-- Modal Panel --}}
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="fileManagerOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-6xl w-full h-[90vh] flex flex-col border border-gray-200">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="p-2 text-indigo-600 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
                            </span>
                            <h3 class="text-lg font-bold text-gray-900">File Manager</h3>
                        </div>
                        <button @click="fileManagerOpen = false" type="button" class="p-2 text-gray-400 transition-colors rounded-full hover:text-gray-600 focus:outline-none bg-gray-50 hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Content (Iframe with Loader) --}}
                    <div class="relative flex-1 bg-gray-50">
                        {{-- Loader --}}
                        <div class="absolute inset-0 z-0 flex items-center justify-center pointer-events-none">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span class="text-xs font-medium text-gray-500">Loading Files...</span>
                            </div>
                        </div>

                        {{-- Iframe --}}
                        <template x-if="fileManagerOpen">
                            <iframe src="{{ route('admin.fm.index') }}" class="absolute inset-0 z-10 w-full h-full bg-white border-none" title="File Manager"></iframe>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @stack('scripts')
</body>
</html>
