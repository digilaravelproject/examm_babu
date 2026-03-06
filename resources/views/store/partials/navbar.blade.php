@php

    // 2. POPULAR TEST SERIES
    $popularTestSeries = [
        [
            'title' => 'SSC GD Constable 2026 Mock Test Series',
            'users' => '285.9k',
            'total_tests' => '779',
            'free_tests' => '11',
            'languages' => ['English', 'Hindi', 'Marathi', 'Telugu', 'Tamil', '+4 More'],
            'features' => ['1 Scholarship Test', '7 🟢 Live Test', '45 SSC CGL 2025 Similar PYP'],
            'more_count' => '+726 more tests',
        ],
        [
            'title' => 'SSC CPO Mock Test Series 2025 (Tier I & II)',
            'subtitle' => '(DP SI & CAPF) (New Pattern)',
            'users' => '488.3k',
            'total_tests' => '1809',
            'free_tests' => '6',
            'languages' => ['English', 'Hindi'],
            'features' => ['3 🟢 Exam Day Special', '1 🔴 Live Test', '66 PYP - Tier I (New Pattern)'],
            'more_count' => '+1739 more tests',
        ],
        [
            'title' => 'RRB Group D Mock Test Series 2024-25',
            'subtitle' => '(Updated Pattern)',
            'users' => '2291.8k',
            'total_tests' => '2104',
            'free_tests' => '48',
            'languages' => ['English', 'Hindi', 'Bengali', 'Marathi', '+7 More'],
            'features' => ['6 Official Mock Based Full Test', '24 Exam Day Special', '158 विज्ञान Express Mahapack'],
            'more_count' => '+1916 more tests',
        ],
        [
            'title' => 'Delhi Police Constable (Executive) 2025',
            'users' => '1002.4k',
            'total_tests' => '1163',
            'free_tests' => '30',
            'languages' => ['English', 'Hindi'],
            'features' => ['29 🔴 Ultimate Live Test', '17 रक्षक Revision Series', '146 Most Saved CTs'],
            'more_count' => '+971 more tests',
        ],
    ];

    // 3. STATS DATA
    $stats = [
        ['count' => '53,567', 'label' => 'Total Selections', 'icon' => '🏆', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
        ['count' => '19,054', 'label' => 'Selections in SSC', 'icon' => '🏛️', 'color' => 'text-brand-blue', 'bg' => 'bg-blue-100'],
        ['count' => '18,921', 'label' => 'Selections in Banking', 'icon' => '🏦', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
        ['count' => '7,087', 'label' => 'Selections in Railways', 'icon' => '🚆', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
        ['count' => '8,505', 'label' => 'Other Govt Exams', 'icon' => '🎖️', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
    ];

    // 4. MOCK TESTS TABS
    $popularTabs = ['Engineering', 'Civil Services', 'Banking', 'Teaching', 'SSC', 'Railways'];

    $mockTests = [
        'Engineering' => [
            ['title' => 'AE SE Group A Mock Test 2', 'subtitle' => 'Revised Pattern April 25', 'price' => 100, 'users' => '12.5k', 'tags' => ['Civil', 'MPSC']],
            ['title' => 'BMC SUB ENGINEER (Civil)', 'subtitle' => 'Full Length Test Series', 'price' => 100, 'users' => '8.2k', 'tags' => ['BMC', 'Civil']],
            ['title' => 'JUNIOR ENGINEER MOCK TEST 1', 'subtitle' => 'Comprehensive JE Pack', 'price' => 200, 'users' => '25k', 'tags' => ['JE', 'Tech']],
            ['title' => 'GATE ME 2026 Foundation', 'subtitle' => 'Chapter-wise Tests', 'price' => 499, 'users' => '5k', 'tags' => ['GATE', 'Mech']],
            ['title' => 'RRB JE Electrical', 'subtitle' => 'Previous Year Papers', 'price' => 150, 'users' => '18k', 'tags' => ['RRB', 'Elec']],
            ['title' => 'SSC JE Civil Mains', 'subtitle' => 'Mains Special Batch', 'price' => 299, 'users' => '9k', 'tags' => ['SSC', 'Civil']],
        ],
        'Civil Services' => [
            ['title' => 'MPSC Rajyaseva Prelims', 'subtitle' => 'GS Paper 1 + CSAT', 'price' => 299, 'users' => '50k', 'tags' => ['MPSC', 'GS']],
            ['title' => 'UPSC CSE GS Mock 1', 'subtitle' => 'All India Rank Test', 'price' => 0, 'users' => '1.2L', 'tags' => ['UPSC', 'Free']],
            ['title' => 'BPSC 70th Prelims', 'subtitle' => 'Bihar Special GK Included', 'price' => 199, 'users' => '30k', 'tags' => ['BPSC', 'State']],
            ['title' => 'UPPSC RO/ARO Series', 'subtitle' => 'Hindi + GS', 'price' => 149, 'users' => '22k', 'tags' => ['UPPSC', 'RO']],
        ],
        'default' => [
            ['title' => 'General Awareness Booster', 'subtitle' => 'Current Affairs 2025', 'price' => 49, 'users' => '2L', 'tags' => ['GK', 'All Exams']],
            ['title' => 'Quantitative Aptitude', 'subtitle' => 'Topic Wise Tests', 'price' => 99, 'users' => '1.5L', 'tags' => ['Maths', 'Practice']],
            ['title' => 'English Language Master', 'subtitle' => 'Grammar + Vocab', 'price' => 99, 'users' => '1.2L', 'tags' => ['English', 'Lang']],
            ['title' => 'Reasoning Ability', 'subtitle' => 'Puzzle & Seating Arrangement', 'price' => 99, 'users' => '1.3L', 'tags' => ['Logic', 'Reasoning']],
        ],
    ];

    // 5. SEO LINKS FOOTER DATA
    $allTestSeries = [
        'Popular' => ['JEE Main 2025', 'CUET 2025', 'NEET 2025', 'SSC GD Constable', 'RRB NTPC', 'IBPS Clerk', 'NDA'],
        'Engineering' => ['JEE Advanced', 'GATE 2025', 'NHPC JE', 'ISRO Scientist', 'BARC', 'DRDO STA', 'NIMCET', 'WB JEE'],
        'Banking' => ['SBI PO', 'IBPS PO', 'RBI Grade B', 'LIC AAO', 'NABARD', 'RBI Assistant', 'BSPHCL Clerk'],
        'SSC & Railways' => ['SSC CGL', 'SSC CHSL', 'SSC MTS', 'SSC CPO', 'RRB Group D', 'RPF SI', 'Delhi Police Driver'],
        'Teaching' => ['CTET 2025', 'UGC NET Paper 1', 'CSIR NET', 'KVS', 'REET', 'UPTET', 'Bihar Teacher'],
        'State Exams' => ['UPSSSC Junior Assistant', 'BPSC AEDO', 'MP GK', 'RPSC', 'MPSC', 'Haryana CET', 'Bihar Police'],
    ];
@endphp

<header x-data="{
        mobileOpen: false,
        scrolled: false,
        megaMenu: null,
        // Check PHP variable, default is false (Dark Text mode)
        isLight: {{ isset($lightNavbar) && $lightNavbar ? 'true' : 'false' }}
    }"
    @scroll.window="scrolled = (window.pageYOffset > 20)"
    class="fixed top-0 z-50 w-full transition-all duration-300"
    :class="(scrolled || !isLight)
        ? 'bg-white/95 backdrop-blur-md shadow-sm py-2 text-slate-800'
        : 'bg-transparent py-4 text-white'">

    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">

            {{-- LEFT SIDE: Logo & Desktop Menu --}}
            <div class="flex items-center gap-8">

                {{-- 1. Logo --}}
                <a href="/" class="relative z-10 block group">
                    <img src="{{ asset('storage/site_images/logo1dotcom.png') }}" alt="ExamBabu"
                        class="object-cover w-12 h-12 transition-transform duration-300 border-2 rounded-full shadow-lg group-hover:rotate-12"
                        :class="(scrolled || !isLight) ? 'border-white' : 'border-transparent'">
                </a>

                {{-- 2. Desktop Navigation --}}
                <nav class="hidden gap-1 md:flex">

                    {{-- Mega Menu Trigger --}}
                    <div class="relative" @mouseenter="megaMenu = 'exams'" @mouseleave="megaMenu = null">
                        <button class="flex items-center gap-1 px-4 py-2 text-sm font-bold transition-all rounded-full"
                            :class="(scrolled || !isLight)
                                ? 'text-slate-700 hover:bg-slate-100'
                                : 'text-white/90 hover:bg-white/10'">
                            Exams
                            <svg class="w-4 h-4 transition-transform duration-300"
                                :class="megaMenu === 'exams' ? 'rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Mega Menu Dropdown --}}
                        <div x-show="megaMenu === 'exams'"
                            x-init="$watch('megaMenu', value => { if(value === 'exams' && !activeCat) activeCat = Object.keys({{ json_encode($examCategories) }})[0] })"
                            x-data="{ activeCat: null }"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-4"
                            class="absolute left-0 top-full mt-2 w-[850px] bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50 flex h-[500px]"
                            style="left: -100px; display: none;">

                            {{-- Sidebar --}}
                            <div class="w-1/3 py-3 overflow-y-auto border-r bg-slate-50 border-slate-100 custom-scrollbar">
                                @foreach ($examCategories as $catName => $data)
                                    <button
                                        @mouseenter="activeCat = '{{ $catName }}'"
                                        @click="window.location.href = '{{ route('store.categories.show', $data['slug']) }}'"
                                        class="flex items-center w-full gap-3 px-5 py-3 text-sm font-bold text-left transition-all duration-200 border-l-4"
                                        :class="activeCat === '{{ $catName }}' ? 'bg-white shadow-sm border-[var(--brand-blue)] text-[var(--brand-blue)]' : 'text-slate-600 border-transparent hover:bg-slate-100'">

                                        @if($data['icon'])
                                            <img src="{{ $data['icon'] }}" class="object-contain w-10 h-10 rounded-full" alt="{{ $catName }}">
                                        @else
                                            <div class="flex items-center justify-center w-10 h-10 font-bold text-white rounded-full bg-slate-300 text-md">
                                                {{ $data['first_letter'] }}
                                            </div>
                                        @endif

                                        {{ $catName }}
                                        <svg x-show="activeCat === '{{ $catName }}'" class="w-4 h-4 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @endforeach
                            </div>

                            {{-- Content Area --}}
                            <div class="w-2/3 p-6 overflow-y-auto bg-white custom-scrollbar">
                                @foreach ($examCategories as $catName => $data)
                                    <div x-show="activeCat === '{{ $catName }}'" class="flex flex-col h-full" style="display: none;">
                                        <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-100">
                                            <h3 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
                                                @if($data['icon'])
                                                    <img src="{{ $data['icon'] }}" class="object-contain w-10 h-10 rounded-full" alt="{{ $catName }}">
                                                @else
                                                    <div class="flex items-center justify-center w-10 h-10 font-bold text-white rounded-full bg-slate-300 text-md">
                                                        {{ $data['first_letter'] }}
                                                    </div>
                                                @endif
                                                Popular {{ $catName }}
                                            </h3>
                                            <a href="{{ route('store.categories.show', $data['slug']) }}" class="text-xs font-bold text-blue-600 hover:underline">View All</a>
                                        </div>

                                        <div class="space-y-3" x-data="{ openSub: null }">
                                            @foreach ($data['subcategories'] as $sub)
                                                <div class="border rounded-lg border-slate-100">

                                                    {{-- Subcategory Row --}}
                                                    <div class="flex items-center justify-between px-4 py-3 transition cursor-pointer hover:bg-slate-50"
                                                        @if (count($sub['micro_categories']) > 0)
                                                            @click="openSub === {{ $sub['id'] }} ? openSub = null : openSub = {{ $sub['id'] }}"
                                                        @else
                                                            @click="window.location.href = '{{ route('exam_details.subcategory', $sub['id']) }}'"
                                                        @endif
                                                    >

                                                        {{-- Subcategory Label --}}
                                                        <span class="text-sm font-semibold text-slate-700 hover:text-[var(--brand-blue)]">
                                                            {{ $sub['name'] }}
                                                        </span>

                                                        {{-- Arrow ONLY if micro category exists --}}
                                                        @if (count($sub['micro_categories']) > 0)
                                                            <div class="text-slate-400">
                                                                <svg class="w-4 h-4 transition-transform duration-200"
                                                                    :class="openSub === {{ $sub['id'] }} ? 'rotate-180' : ''"
                                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                            </div>
                                                        @else
                                                            <div class="text-slate-300">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Micro Categories --}}
                                                    @if (count($sub['micro_categories']) > 0)
                                                        <div x-show="openSub === {{ $sub['id'] }}"
                                                            x-collapse
                                                            class="px-6 py-2 pb-4 space-y-2 bg-slate-50">

                                                            @foreach ($sub['micro_categories'] as $micro)
                                                                <a href="{{ route('exam_details.microcategory', [$sub['id'], $micro['id']]) }}"
                                                                    class="flex items-center justify-between text-sm text-slate-600 hover:text-[var(--brand-blue)] hover:translate-x-1 transition">
                                                                        <span>• {{ $micro['name'] }}</span>
                                                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                                        </svg>
                                                                </a>
                                                            @endforeach

                                                        </div>
                                                    @endif

                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Simple Link --}}
                    <!--<a href="#" class="px-4 py-2 text-sm font-bold transition-colors rounded-full"-->
                    <!--    :class="(scrolled || !isLight) ? 'text-slate-700 hover:bg-slate-100' : 'text-white/90 hover:bg-white/10'">-->
                    <!--    Test Series-->
                    <!--</a>-->
                </nav>
            </div>

            {{-- RIGHT SIDE: Search, Auth, Mobile Toggle --}}
            <div class="flex items-center gap-4">

                {{-- Search Bar --}}
                <div class="relative hidden lg:flex group">
                    <input type="text" placeholder="Search exams..."
                        class="w-56 py-2 pl-10 pr-4 text-sm font-medium transition-all duration-300 border border-transparent rounded-full focus:ring-2 focus:w-64"
                        :class="(scrolled || !isLight)
                            ? 'bg-slate-100 text-slate-700 focus:bg-white shadow-inner focus:ring-blue-500'
                            : 'bg-white/10 text-white placeholder-blue-200 focus:bg-white/20 border-white/10 focus:ring-white/30'">

                    <svg class="absolute w-5 h-5 transition-colors left-3 top-2"
                         :class="(scrolled || !isLight) ? 'text-slate-400' : 'text-blue-200'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                {{-- Auth Buttons --}}
                @auth
                    <a href="{{ route('home') }}" class="text-sm font-bold"
                       :class="(scrolled || !isLight) ? 'text-slate-700 hover:text-blue-600' : 'text-white hover:text-blue-200'">
                       Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden px-4 text-sm font-bold transition-colors sm:block"
                        :class="(scrolled || !isLight) ? 'text-slate-700 hover:text-blue-600' : 'text-white hover:text-blue-200'">
                        Log in
                    </a>

                    <a href="{{ route('register') }}"
                        class="px-5 py-2 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all transform"
                        :class="(scrolled || !isLight)
                            ? 'text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-blue-500/30'
                            : 'text-blue-900 bg-white hover:bg-blue-50 shadow-black/20'">
                        Sign up
                    </a>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="p-2 md:hidden"
                    :class="(scrolled || !isLight) ? 'text-slate-600' : 'text-white'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- MOBILE MENU DRAWER --}}
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-0 z-[60] w-full h-screen overflow-y-auto bg-white md:hidden"
         @click.self="mobileOpen = false"
         style="display: none;">

        <div class="sticky top-0 z-50 flex items-center justify-between p-4 border-b border-slate-100 bg-white">
            <span class="text-xl font-bold text-slate-800">Menu</span>
            <button @click="mobileOpen = false" class="p-2 rounded-full text-slate-500 hover:bg-slate-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-4 pb-20 space-y-3" x-data="{ openCat: null, openSub: null }">

            <div class="text-xs font-bold tracking-widest uppercase text-slate-400 mb-2 px-2">Browse Exams</div>

            <div class="space-y-2">
                @foreach ($examCategories as $catName => $data)
                    <div class="border rounded-xl border-slate-100 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50/50" @click="openCat === '{{ $catName }}' ? openCat = null : openCat = '{{ $catName }}'">
                            <div class="flex items-center gap-3">
                                @if($data['icon'])
                                    <img src="{{ $data['icon'] }}" class="w-8 h-8 rounded-full object-contain" alt="">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-500">{{ $data['first_letter'] }}</div>
                                @endif
                                <span class="font-bold text-slate-700">{{ $catName }}</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform text-slate-400" :class="openCat === '{{ $catName }}' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>

                        <div x-show="openCat === '{{ $catName }}'" x-collapse class="bg-white border-t border-slate-50">
                            @foreach ($data['subcategories'] as $sub)
                                <div class="border-b last:border-0 border-slate-50">
                                    <div class="flex items-center justify-between px-6 py-3" @click="openSub === {{ $sub['id'] }} ? openSub = null : openSub = {{ $sub['id'] }}">
                                        <span class="text-sm font-semibold text-slate-600">{{ $sub['name'] }}</span>
                                        @if(count($sub['micro_categories']) > 0)
                                            <svg class="w-3 h-3 transition-transform text-slate-300" :class="openSub === {{ $sub['id'] }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        @else
                                            <a href="{{ route('exam_details.subcategory', $sub['id']) }}" class="text-blue-500">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>

                                    @if(count($sub['micro_categories']) > 0)
                                        <div x-show="openSub === {{ $sub['id'] }}" x-collapse class="bg-slate-50 px-8 py-2 space-y-2">
                                            @foreach ($sub['micro_categories'] as $micro)
                                                <a href="{{ route('exam_details.microcategory', [$sub['id'], $micro['id']]) }}" class="block py-2 text-sm text-slate-500 active:text-blue-600">• {{ $micro['name'] }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            <a href="{{ route('store.categories.show', $data['slug']) }}" class="block p-4 text-center text-xs font-bold text-blue-600 bg-blue-50/50">View all in {{ $catName }}</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="h-px my-6 bg-slate-100"></div>

            {{-- Auth Buttons --}}
            <div class="space-y-3">
                @auth
                    <a href="{{ route('home') }}" class="block w-full py-3 px-4 font-bold text-center text-white rounded-lg transition-all hover:shadow-lg" style="background-color: var(--brand-blue);">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block w-full py-3 px-4 font-bold text-center border-2 border-blue-600 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="block w-full py-3 px-4 font-bold text-center text-white rounded-lg transition-all hover:shadow-lg" style="background-color: var(--brand-blue);">
                        Sign up
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>
