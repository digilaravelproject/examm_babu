<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Exam Babu') }} - Student Portal</title>

    <link rel="icon" type="image/png" href="{{ asset('storage/site_images/logo1dotcom.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        :root {
            --brand-blue: #0777be;
            --brand-pink: #f062a4;
            --brand-green: #94c940;
            --brand-sky: #7fd2ea;
            --sidebar-bg: #0f172a;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
    </style>
</head>

<body class="h-full font-sans antialiased text-slate-900 bg-slate-50"
      x-data="{
          sidebarOpen: false,
          currentSyllabus: null,
          init() {
              fetch('{{ route('student.get_current_syllabus') }}')
                  .then(response => response.json())
                  .then(data => {
                      this.currentSyllabus = data.status ? data.name : null;
                  })
                  .catch(() => { this.currentSyllabus = null; });
          }
      }">

    <div class="flex h-screen overflow-hidden bg-slate-50">

        <div x-show="sidebarOpen" x-cloak class="relative z-50 md:hidden" role="dialog" aria-modal="true">

            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"
                 @click="sidebarOpen = false"></div>

            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 flex flex-col w-full max-w-xs bg-white shadow-2xl">

                <div class="flex items-center justify-between h-16 px-6 border-b border-slate-100">
                    <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('storage/site_images/logo1dotcom.png') }}"
                             alt="ExamBabu"
                             class="object-cover w-10 h-10 border rounded-full border-slate-200">
                        <span class="text-xl font-extrabold tracking-tight text-slate-800">
                            Exam<span style="color: var(--brand-blue);">Babu</span>
                        </span>
                    </a>

                    <button @click="sidebarOpen = false" class="p-1 rounded-full text-slate-400 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 px-4 py-6 overflow-y-auto sidebar-scroll">
                    @include('layouts.partials.student_sidebar_nav')
                </div>
            </div>
        </div>

        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-72">
                <div class="flex flex-col flex-1 min-h-0 bg-white border-r shadow-sm border-slate-200">
                    <div class="flex items-center flex-shrink-0 h-20 px-6 border-b border-slate-50">
                        <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3">
                            <img src="{{ asset('storage/site_images/logo1dotcom.png') }}"
                                 alt="ExamBabu"
                                 class="object-cover w-12 h-12 border rounded-full shadow-sm border-slate-200">

                            <span class="text-2xl font-extrabold tracking-tight text-slate-800">
                                Exam<span style="color: var(--brand-blue);">Babu</span>
                            </span>
                        </a>
                    </div>
                    <div class="flex flex-col flex-1 px-5 py-6 overflow-y-auto sidebar-scroll">
                        @include('layouts.partials.student_sidebar_nav')
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            <header class="sticky top-0 z-20 flex items-center justify-between h-20 px-6 border-b border-slate-200 bg-white/90 backdrop-blur-md md:px-8">

                <button @click="sidebarOpen = true" class="transition-colors text-slate-500 md:hidden focus:outline-none" style="hover:color: var(--brand-blue);">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="justify-start flex-1 hidden ml-4 sm:flex">
                    <div class="relative w-full max-w-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                            class="block w-full py-2.5 pl-10 pr-3 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none transition-all shadow-sm"
                            style="focus:border-color: var(--brand-blue); focus:ring: 2px solid var(--brand-blue);"
                            placeholder="Search exams...">
                    </div>
                </div>

                <div class="flex items-center ml-auto space-x-6">
                    <div class="relative" x-data="{ dropdownOpen: false }">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-3 focus:outline-none group">

                            @if(Auth::user()->profile_photo_path)
                                <img class="object-cover w-10 h-10 transition-all border-2 border-white rounded-full shadow-sm group-hover:border-blue-100"
                                    src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                    alt="{{ Auth::user()->fullName }}">
                            @else
                                <img class="object-cover w-10 h-10 transition-all border-2 border-white rounded-full shadow-sm group-hover:border-blue-100"
                                    src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->fullName) }}&color=FFFFFF&background=0777be&font-size=0.33&bold=true"
                                    alt="{{ Auth::user()->fullName }}">
                            @endif

                            <div class="hidden text-left md:block">
                                <p class="text-sm font-bold leading-tight text-slate-800">{{ Auth::user()->fullName }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ Auth::user()->roles->first()->name ?? 'Student' }}</p>
                            </div>

                            <svg class="w-4 h-4 transition-colors text-slate-400 group-hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak
                            class="absolute right-0 z-50 w-56 mt-3 overflow-hidden origin-top-right bg-white shadow-2xl rounded-xl ring-1 ring-black ring-opacity-5">

                            <div class="px-5 py-4 border-b bg-slate-50 border-slate-100">
                                <p class="text-sm font-bold truncate text-slate-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs truncate text-slate-500">{{ Auth::user()->email }}</p>
                            </div>

                            <div class="py-2">
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors" style="hover:color: var(--brand-blue);">Edit Profile</a>
                                <a href="{{ route('student.subscriptions.index') }}" class="flex items-center px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors" style="hover:color: var(--brand-blue);">Subscriptions</a>
                                <a href="{{ route('student.payments.index') }}" class="flex items-center px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors" style="hover:color: var(--brand-blue);">Billing History</a>
                            </div>

                            <div class="py-2 border-t border-slate-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center px-5 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">Log Out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="relative flex-1 overflow-y-auto focus:outline-none bg-slate-50">
                <div class="py-8">
                    @if(session('success'))
                        <div class="px-4 mx-auto mb-6 max-w-7xl sm:px-6 md:px-8">
                            <div class="p-4 text-sm font-bold text-green-700 border border-green-200 shadow-sm bg-green-50 rounded-xl">
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="px-4 mx-auto mb-6 max-w-7xl sm:px-6 md:px-8">
                            <div class="p-4 text-sm font-bold text-red-700 border border-red-200 shadow-sm bg-red-50 rounded-xl">
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif

                    <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
