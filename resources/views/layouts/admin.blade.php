<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Exam Babu') }}</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/favicon.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --brand-blue: #0777be;
            --brand-pink: #f062a4;
            --brand-green: #94c940;
            --brand-sky: #7fd2ea;
            --sidebar-bg: #0f172a;
        }

        [x-cloak] {
            display: none !important;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: #1e293b;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }

        .nav-link-active {
            background-color: var(--brand-blue) !important;
            color: white !important;
            border-left: 4px solid var(--brand-green);
        }

        .sub-link-active {
            color: #7fd2ea !important;
            font-weight: 700;
        }

        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-600 bg-gray-50">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, fileManagerOpen: false }">

        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 h-full transition-all duration-300 transform border-r bg-slate-900 border-slate-800 md:static md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <div class="flex items-center justify-center h-16 px-4 border-b bg-slate-950 border-slate-800">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-auto max-h-10">
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scroll">

                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
                    class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.dashboard*') ? 'nav-link-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>

                <button @click="fileManagerOpen = true"
                    class="flex items-center w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    File Manager
                </button>

                <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Engagement
                </div>

                <div x-data="{ open: {{ request()->routeIs('admin.quizzes.*', 'admin.exam.*', 'admin.quiz-types.*', 'admin.exam-types.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            Manage Tests
                        </div>
                        <svg :class="open ? 'rotate-180 text-brand-green' : ''" class="w-4 h-4 transition-transform"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                        <a href="{{ Route::has('admin.quizzes.index') ? route('admin.quizzes.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.quizzes.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Quizzes</a>
                        <a href="{{ Route::has('admin.exam.index') ? route('admin.exam.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.exam.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Exams</a>
                        <a href="{{ Route::has('admin.quiz-types.index') ? route('admin.quiz-types.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.quiz-types.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Quiz
                            Types</a>
                        <a href="{{ Route::has('admin.exam-types.index') ? route('admin.exam-types.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.exam-types.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Exam
                            Types</a>
                    </div>
                </div>

                <div x-data="{ open: {{ request()->routeIs('admin.practice-sets.*', 'admin.lessons.*', 'admin.videos.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            Manage Learning
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                        <a href="{{ Route::has('admin.practice-sets.index') ? route('admin.practice-sets.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.practice-sets.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Practice
                            Sets</a>
                        <a href="{{ Route::has('admin.lessons.index') ? route('admin.lessons.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.lessons.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Lessons</a>
                        <a href="{{ Route::has('admin.videos.index') ? route('admin.videos.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.videos.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Videos</a>
                    </div>
                </div>

                <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Library</div>

                <div x-data="{ open: {{ request()->routeIs('admin.questions.*', 'admin.comprehensions.*', 'admin.question-types.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            Question Bank
                        </div>
                        <svg :class="open ? 'rotate-180 text-brand-green' : ''" class="w-4 h-4 transition-transform"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                        <a href="{{ Route::has('admin.questions.index') ? route('admin.questions.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.questions.index') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Questions</a>
                        <a href="{{ Route::has('admin.questions.import') ? route('admin.questions.import') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.questions.import') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Import
                            Questions</a>
                        <a href="{{ Route::has('admin.comprehensions.index') ? route('admin.comprehensions.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.comprehensions.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Comprehensions</a>
                        <a href="{{ Route::has('admin.question-types.index') ? route('admin.question-types.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.question-types.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Question
                            Types</a>
                    </div>
                </div>

                <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Master Data
                </div>

                <div x-data="{ open: {{ request()->routeIs('admin.categories.*', 'admin.sub_categories.*', 'admin.tags.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            Manage Categories
                        </div>
                        <svg :class="open ? 'rotate-180 text-brand-green' : ''" class="w-4 h-4 transition-transform"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                        <a href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.categories.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Categories</a>
                        <a href="{{ Route::has('admin.sub-categories.index') ? route('admin.sub-categories.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.sub-categories.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Sub
                            Categories</a>
                        <a href="{{ Route::has('admin.tags.index') ? route('admin.tags.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.tags.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Tags</a>
                    </div>
                </div>

                <div x-data="{ open: {{ request()->routeIs('admin.sections.*', 'admin.skills.*', 'admin.topics.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            Manage Subjects
                        </div>
                        <svg :class="open ? 'rotate-180 text-brand-green' : ''" class="w-4 h-4 transition-transform"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-collapse class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                        <a href="{{ Route::has('admin.sections.index') ? route('admin.sections.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.sections.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Sections</a>
                        <a href="{{ Route::has('admin.skills.index') ? route('admin.skills.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.skills.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Skills</a>
                        <a href="{{ Route::has('admin.topics.index') ? route('admin.topics.index') : '#' }}"
                            class="block px-8 py-2 text-sm {{ request()->routeIs('admin.topics.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Topics</a>
                    </div>
                </div>
                <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">System</div>

                <a href="{{ Route::has('admin.roles_permissions.index') ? route('admin.roles_permissions.index') : '#' }}"
                    class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all {{ request()->routeIs('admin.roles_permissions.*') ? 'nav-link-active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    Roles & Permissions
                </a>

                <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}"
                    class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Manage Users
                </a>

                <a href="{{ Route::has('admin.settings') ? route('admin.settings') : '#' }}"
                    class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                    </svg>
                    Settings
                </a>

            </nav>

            <div class="p-4 border-t bg-slate-950/50 border-slate-800">
                <div class="flex items-center p-2 border rounded-xl bg-slate-900/50 border-slate-800">
                    <div class="flex-shrink-0">
                        <div
                            class="flex items-center justify-center text-sm font-bold text-white rounded-lg shadow-lg w-9 h-9 bg-gradient-to-tr from-blue-600 to-pink-500 shadow-blue-900/20">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="flex-1 ml-3 overflow-hidden">
                        <p class="text-[11px] font-bold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] text-slate-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-1">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex flex-col flex-1 h-full overflow-hidden">
            <header class="flex items-center justify-between h-16 px-8 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 md:hidden"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg></button>
                    <h2 class="text-lg font-bold text-gray-800">@yield('header', 'Admin Panel')</h2>
                </div>
            </header>

            <main class="flex-1 p-6 overflow-y-auto bg-gray-50 custom-scroll">
                @yield('content')
            </main>
        </div>

        <div x-show="fileManagerOpen" x-cloak class="fixed inset-0 z-[100] overflow-hidden">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="fileManagerOpen = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[85vh] flex flex-col border border-gray-200">
                    <div class="flex items-center justify-between px-6 py-4 border-b">
                        <h3 class="font-bold text-gray-900">File Manager</h3>
                        <button @click="fileManagerOpen = false" class="text-gray-400 hover:text-red-500"><svg
                                class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>
                    <div class="flex-1 bg-gray-50">
                        <iframe src="{{ route('admin.fm.index') }}" class="w-full h-full border-none"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
