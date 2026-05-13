<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic Title based on Role --}}
    <title>@yield('title', ucfirst(request()->route('role')) . ' Panel') - {{ config('app.name', 'Exam Babu') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('storage/site_images/logo1dotcom.png') }}">
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

        /* Form Inputs */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-600 bg-gray-50">
    @if (session()->has('impersonator_id'))
        <div
            class="fixed top-0 left-0 right-0 z-[9999] bg-red-600 text-white text-center px-4 py-2 shadow-md flex items-center justify-center gap-4">
            <span class="text-sm font-bold animate-pulse">
                ⚠️ You are impersonating {{ Auth::user()->fullname }}
            </span>
            <form action="{{ route('impersonation.stop') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit"
                    class="px-3 py-1 text-xs font-bold tracking-wider text-red-600 uppercase transition-colors bg-white rounded shadow-sm hover:bg-gray-100">
                    Return to Admin Panel
                </button>
            </form>
        </div>
        {{-- Spacer to push content down so it doesn't get hidden behind the fixed banner --}}
        <div class="h-10"></div>
    @endif

    @php
        // 1. Capture Current Role from URL
        $currentRole = request()->route('role') ?? 'instructor'; // Fallback to instructor if missing

        // 2. Active Menu Logic (Checks for 'panel.*' routes)
        $activeMenu = '';

        if (request()->routeIs('panel.exams*', 'panel.exam-types*')) {
            $activeMenu = 'engagement';
        } elseif (request()->routeIs('panel.questions.*', 'panel.questions.import', 'panel.comprehensions.*')) {
            $activeMenu = 'library';
        } elseif (request()->routeIs('panel.categories.*', 'panel.tags.*', 'panel.sub-categories.*')) {
            $activeMenu = 'master';
        } elseif (request()->routeIs('panel.sections.*', 'panel.skills.*', 'panel.topics.*')) {
            $activeMenu = 'subjects';
        } elseif (request()->routeIs('panel.plans.*', 'panel.subscriptions.*', 'panel.payments.*')) {
            $activeMenu = 'config';
        } elseif (request()->routeIs('panel.users.*', 'panel.roles_permissions.*')) {
            $activeMenu = 'users';
        } elseif (
            request()->routeIs('panel.referrals.history', 'panel.referrals.withdrawals', 'panel.settings.referral')
        ) {
            $activeMenu = 'referral_manage';
        }
    @endphp

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, activeDropdown: '{{ $activeMenu }}' }">

        {{-- SIDEBAR --}}
        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 h-full transition-all duration-300 transform border-r bg-slate-900 border-slate-800 md:static md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="flex items-center h-16 gap-3 px-6 border-b border-slate-800"
                style="background-color: var(--sidebar-bg);">
                <img src="{{ asset('storage/site_images/logo1dotcom.png') }}" alt="ExamBabu"
                    class="object-cover w-10 h-10 border rounded-full shadow-sm border-slate-700">
                <span class="text-xl font-bold tracking-wide text-white">
                    Exam<span style="color: var(--brand-blue);">Babu</span>
                    <span class="text-[10px] text-slate-400 block font-normal capitalize">{{ $currentRole }}
                        Panel</span>
                </span>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scroll">

                {{-- DASHBOARD --}}
                @can('view dashboard')
                    <a href="{{ Route::has('panel.dashboard') ? route('panel.dashboard', ['role' => $currentRole]) : '#' }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('panel.dashboard') ? 'nav-link-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        Dashboard
                    </a>
                @endcan

                {{-- 1. ENGAGEMENT (Exams) --}}
                @can('manage exams')
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Engagement
                    </div>
                    <div>
                        <button
                            @click="activeDropdown === 'engagement' ? activeDropdown = null : activeDropdown = 'engagement'"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                            :class="activeDropdown === 'engagement' ? 'bg-slate-800 text-white' :
                                'text-slate-400 hover:bg-slate-800 hover:text-white'">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3"
                                    :class="activeDropdown === 'engagement' ? 'text-brand-sky' : 'text-slate-500'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg>
                                Manage Tests
                            </div>
                            <svg :class="activeDropdown === 'engagement' ? 'rotate-180 text-brand-green' : ''"
                                class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'engagement'" x-cloak x-collapse
                            class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">

                            <a href="{{ Route::has('panel.exams.index') ? route('panel.exams.index', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.exams.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                Exams
                            </a>

                            {{-- ADDED EXAM TYPES HERE --}}
                            <a href="{{ Route::has('panel.exam-types.index') ? route('panel.exam-types.index', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.exam-types.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                Exam Types
                            </a>

                        </div>
                    </div>
                @endcan

                {{-- 2. LIBRARY (Questions) --}}
                @can('manage questions')
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Library</div>
                    <div>
                        <button @click="activeDropdown === 'library' ? activeDropdown = null : activeDropdown = 'library'"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                            :class="activeDropdown === 'library' ? 'bg-slate-800 text-white' :
                                'text-slate-400 hover:bg-slate-800 hover:text-white'">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3"
                                    :class="activeDropdown === 'library' ? 'text-brand-sky' : 'text-slate-500'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                Question Bank
                            </div>
                            <svg :class="activeDropdown === 'library' ? 'rotate-180 text-brand-green' : ''"
                                class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'library'" x-cloak x-collapse
                            class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">

                            <a href="{{ Route::has('panel.questions.index') ? route('panel.questions.index', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.questions.index') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                Questions
                            </a>

                            @can('import questions')
                                <a href="{{ Route::has('panel.questions.import') ? route('panel.questions.import', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.questions.import') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                    Import Questions
                                </a>
                            @endcan

                            <a href="{{ Route::has('panel.comprehensions.index') ? route('panel.comprehensions.index', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.comprehensions.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                Comprehensions
                            </a>
                        </div>
                    </div>
                @endcan

                {{-- 3. MASTER DATA (Taxonomy) --}}
                @if (auth()->user()->can('manage categories') || auth()->user()->can('manage tags'))
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Master
                        Data</div>
                    <div>
                        <button @click="activeDropdown === 'master' ? activeDropdown = null : activeDropdown = 'master'"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                            :class="activeDropdown === 'master' ? 'bg-slate-800 text-white' :
                                'text-slate-400 hover:bg-slate-800 hover:text-white'">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3"
                                    :class="activeDropdown === 'master' ? 'text-brand-sky' : 'text-slate-500'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                    </path>
                                </svg>
                                Manage Categories
                            </div>
                            <svg :class="activeDropdown === 'master' ? 'rotate-180 text-brand-green' : ''"
                                class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'master'" x-cloak x-collapse
                            class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">

                            @can('manage categories')
                                <a href="{{ Route::has('panel.categories.index') ? route('panel.categories.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.categories.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                    Categories
                                </a>
                                <a href="{{ Route::has('panel.sub-categories.index') ? route('panel.sub-categories.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.sub-categories.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                    Sub Categories
                                </a>
                                <a href="{{ Route::has('panel.micro-categories.index') ? route('panel.micro-categories.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.micro-categories.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Micro
                                    Categories</a>
                            @endcan

                            {{-- @can('manage tags')
                                <a href="{{ Route::has('panel.tags.index') ? route('panel.tags.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.tags.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">
                                    Tags
                                </a>
                            @endcan --}}
                        </div>
                    </div>
                @endif

                {{-- 4. SUBJECTS --}}
                @if (auth()->user()->can('manage sections') ||
                        auth()->user()->can('manage skills') ||
                        auth()->user()->can('manage topics'))
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Subjects
                    </div>
                    <div>
                        <button
                            @click="activeDropdown === 'subjects' ? activeDropdown = null : activeDropdown = 'subjects'"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                            :class="activeDropdown === 'subjects' ? 'bg-slate-800 text-white' :
                                'text-slate-400 hover:bg-slate-800 hover:text-white'">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3"
                                    :class="activeDropdown === 'subjects' ? 'text-brand-sky' : 'text-slate-500'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                                Manage Subjects
                            </div>
                            <svg :class="activeDropdown === 'subjects' ? 'rotate-180 text-brand-green' : ''"
                                class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'subjects'" x-cloak x-collapse
                            class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                            @can('manage sections')
                                <a href="{{ Route::has('panel.sections.index') ? route('panel.sections.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.sections.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Sections</a>
                            @endcan

                            @can('manage skills')
                                <a href="{{ Route::has('panel.skills.index') ? route('panel.skills.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.skills.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Skills</a>
                            @endcan

                            @can('manage topics')
                                <a href="{{ Route::has('panel.topics.index') ? route('panel.topics.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.topics.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Topics</a>
                            @endcan
                        </div>
                    </div>
                @endif

                {{-- 5. CONFIGURATION (Monetization) --}}
                @can('manage plans')
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">
                        Configuration</div>
                    <div>
                        <button @click="activeDropdown === 'config' ? activeDropdown = null : activeDropdown = 'config'"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                            :class="activeDropdown === 'config' ? 'bg-slate-800 text-white' :
                                'text-slate-400 hover:bg-slate-800 hover:text-white'">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3"
                                    :class="activeDropdown === 'config' ? 'text-brand-sky' : 'text-slate-500'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                    </path>
                                </svg>
                                Monetization
                            </div>
                            <svg :class="activeDropdown === 'config' ? 'rotate-180 text-brand-green' : ''"
                                class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'config'" x-cloak x-collapse
                            class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                            <a href="{{ Route::has('panel.plans.index') ? route('panel.plans.index', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.plans.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Plans</a>
                            @can('manage subscriptions')
                                <a href="{{ Route::has('panel.subscriptions.index') ? route('panel.subscriptions.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.subscriptions.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Subscription</a>
                            @endcan
                            @can('manage payments')
                                <a href="{{ Route::has('panel.payments.index') ? route('panel.payments.index', ['role' => $currentRole]) : '#' }}"
                                    class="block px-8 py-2 text-sm {{ request()->routeIs('panel.payments.*') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Payments</a>
                            @endcan
                        </div>
                    </div>
                @endcan

                {{-- 6. SYSTEM (Users & Roles) --}}
                @if (auth()->user()->can('manage users') || auth()->user()->can('manage roles'))
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">System
                    </div>

                    @can('manage roles')
                        <a href="{{ Route::has('panel.roles_permissions.index') ? route('panel.roles_permissions.index', ['role' => $currentRole]) : '#' }}"
                            class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all {{ request()->routeIs('panel.roles_permissions.*') ? 'nav-link-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                            Roles & Permissions
                        </a>
                    @endcan

                    @can('manage users')
                        <a href="{{ Route::has('panel.users.index') ? route('panel.users.index', ['role' => $currentRole]) : '#' }}"
                            class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-all {{ request()->routeIs('panel.users.*') ? 'nav-link-active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            Manage Users
                        </a>
                    @endcan
                @endif

                {{-- 7. REFERRAL SYSTEM --}}
                @can('access referral')
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Earnings
                    </div>
                    <a href="{{ Route::has('panel.referral.dashboard') ? route('panel.referral.dashboard', ['role' => $currentRole]) : '#' }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('panel.referral.dashboard') ? 'nav-link-active' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        Refer & Earn
                    </a>
                @endcan

                @can('manage referrals')
                    <div class="pt-4 pb-1 pl-4 uppercase text-[10px] font-bold tracking-widest text-slate-600">Referral
                        Management</div>
                    <div>
                        <button
                            @click="activeDropdown === 'referral_manage' ? activeDropdown = null : activeDropdown = 'referral_manage'"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm font-medium rounded-lg transition-all"
                            :class="activeDropdown === 'referral_manage' ? 'bg-slate-800 text-white' :
                                'text-slate-400 hover:bg-slate-800 hover:text-white'">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3"
                                    :class="activeDropdown === 'referral_manage' ? 'text-brand-sky' : 'text-slate-500'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                Manage Payouts
                            </div>
                            <svg :class="activeDropdown === 'referral_manage' ? 'rotate-180 text-brand-green' : ''"
                                class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div x-show="activeDropdown === 'referral_manage'" x-cloak x-collapse
                            class="mx-2 mt-1 space-y-1 rounded-lg bg-slate-800/30">
                            <a href="{{ Route::has('panel.referrals.history') ? route('panel.referrals.history', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.referrals.history') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Referral
                                History</a>
                            <a href="{{ Route::has('panel.referrals.withdrawals') ? route('panel.referrals.withdrawals', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.referrals.withdrawals') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Payout
                                Requests</a>
                            <a href="{{ Route::has('panel.settings.referral') ? route('panel.settings.referral', ['role' => $currentRole]) : '#' }}"
                                class="block px-8 py-2 text-sm {{ request()->routeIs('panel.settings.referral') ? 'sub-link-active' : 'text-slate-400 hover:text-white' }}">Commission
                                Rates</a>
                        </div>
                    </div>
                @endcan

            </nav>

            {{-- User Profile Footer --}}
            <div class="p-4 border-t bg-slate-950/50 border-slate-800">
                <div class="flex items-center p-2 border rounded-xl bg-slate-900/50 border-slate-800">
                    <div class="flex-shrink-0">
                        @if (Auth::user()->profile_photo_path)
                            <img class="object-cover rounded-lg w-9 h-9"
                                src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                alt="{{ Auth::user()->fullname }}">
                        @else
                            <div
                                class="flex items-center justify-center text-sm font-bold text-white rounded-lg shadow-lg w-9 h-9 bg-gradient-to-tr from-blue-600 to-pink-500">
                                {{ substr(Auth::user()->fullname, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 ml-3 overflow-hidden">
                        <p class="text-[11px] font-bold text-white truncate">{{ Auth::user()->fullname }}</p>
                        <p class="text-[9px] text-slate-500 truncate capitalize">{{ $currentRole }}</p>
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

        {{-- MAIN CONTENT --}}
        <div class="flex flex-col flex-1 h-full overflow-hidden">
            <header class="flex items-center justify-between h-16 px-8 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 md:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-bold text-gray-800">@yield('header', 'Dashboard')</h2>
                </div>
            </header>

            <main class="flex-1 p-6 overflow-y-auto bg-gray-50 custom-scroll">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
