@extends('layouts.site')

@php
    // --- DATA BLOCK (Temporary: Move to Controller later) ---

    // 1. MEGA MENU DATA (Needed for Navbar Partial)
    $examCategories = [
        'Police Exams' => [
            'icon' => '👮',
            'grouped' => true,
            'groups' => [
                'Delhi Police' => ['Delhi Police Constable', 'Delhi Police Head Constable', 'Delhi Police Driver', 'Delhi Police MTS'],
                'Uttar Pradesh Police' => ['UP Police SI', 'UP Police Constable', 'UP Police ASI', 'UP Police Jail Warder'],
                'Bihar Police' => ['Bihar Police SI', 'Bihar Police Constable', 'Bihar Police Prohibition SI'],
                'Rajasthan Police' => ['Rajasthan Police SI', 'Rajasthan Police Constable'],
                'Maharashtra Police' => ['Maharashtra Police Constable', 'MPSC PSI'],
            ],
        ],
        'SSC Exams' => ['icon' => '🏛️', 'exams' => ['SSC CGL', 'SSC CHSL', 'SSC MTS', 'SSC CPO', 'SSC GD Constable', 'SSC JE 2025']],
        'Banking Exams' => ['icon' => '🏦', 'exams' => ['SBI PO', 'SBI Clerk', 'IBPS PO', 'IBPS Clerk', 'RBI Grade B', 'IBPS RRB PO']],
        'Teaching Exams' => ['icon' => '👨‍🏫', 'exams' => ['CTET 2025', 'UGC NET 2025', 'CSIR NET 2025', 'KVS', 'Super TET']],
        'Civil Services' => ['icon' => '🇮🇳', 'exams' => ['UPSC CSE 2025', 'UPSC CDS', 'UPSC NDA', 'UPPSC RO ARO', 'BPSC']],
        'Railways Exams' => ['icon' => '🚆', 'exams' => ['RRB Group D', 'RRB NTPC', 'RRB ALP', 'RPF SI', 'RPF Constable']],
        'Engineering' => ['icon' => '🏗️', 'exams' => ['GATE 2025', 'SSC JE', 'RRB JE', 'ISRO Scientist', 'BARC']],
        'Defence Exams' => ['icon' => '🎖️', 'exams' => ['Army Agniveer', 'Airforce Agniveer', 'AFCAT', 'CDS']],
    ];

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

@section('content')

    <section class="relative z-10 px-4 pt-32 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div class="space-y-8" x-data="{ show: false }" x-init="setTimeout(() => show = true, 200)">
                    <div class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold border rounded-full shadow-sm bg-white/80 backdrop-blur-md"
                         style="color: var(--brand-blue); border-color: #bfdbfe;">
                        <span class="live-dot"></span> #1 Trusted Exam Platform
                    </div>

                    <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 leading-[1.1] tracking-tight"
                        x-show="show" x-transition:enter="transition ease-out duration-1000"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Crack Your <br>
                        <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, var(--brand-blue), var(--brand-sky));">
                            Dream Job
                        </span>
                    </h1>

                    <p class="max-w-lg text-xl font-medium leading-relaxed text-slate-600" x-show="show"
                        x-transition:enter="transition ease-out duration-1000 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Join <b>2 Crore+ students</b> preparing for SSC, Banking, Railways & Engineering exams with India's best Super Teachers.
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row" x-show="show"
                        x-transition:enter="transition ease-out duration-1000 delay-400"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="{{ route('register') }}"
                            class="px-8 py-4 text-lg font-bold text-center text-white transition-all shadow-xl rounded-xl hover:shadow-2xl hover:-translate-y-1"
                            style="background-color: var(--brand-blue); box-shadow: 0 10px 15px -3px rgba(7, 119, 190, 0.3);">
                            Start Free Mock Test
                        </a>
                    </div>
                </div>

                <div class="hidden lg:block h-[450px] relative w-full" x-data="{ active: 0 }"
                    x-init="setInterval(() => active = (active + 1) % 3, 3500)">
                    <div class="absolute inset-0 transition-all duration-700 ease-out"
                        :class="active === 0 ? 'opacity-100 translate-x-0 scale-100 z-30' : 'opacity-0 translate-x-10 scale-95 z-0'">
                        <div class="p-10 text-white shadow-2xl rounded-[2rem] h-full relative overflow-hidden card-3d flex flex-col justify-center border-0"
                             style="background: linear-gradient(to bottom right, var(--brand-blue), #60a5fa);">
                            <div class="absolute text-6xl top-10 right-10 opacity-30 animate-bounce" style="animation-duration: 3s">🏛️</div>
                            <div class="absolute text-5xl bottom-10 right-20 opacity-30 animate-pulse">🇮🇳</div>

                            <span class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">TRENDING NOW</span>
                            <h3 class="relative z-10 mb-4 text-4xl font-bold">SSC CGL 2025</h3>
                            <p class="relative z-10 max-w-xs mb-8 text-lg text-indigo-100">Target 350+ Score with India's most attempted mock series.</p>
                            <button class="relative z-10 px-6 py-3 font-bold transition bg-white shadow-lg rounded-xl w-fit" style="color: var(--brand-blue);">View Test Series</button>
                        </div>
                    </div>
                    <div class="absolute inset-0 transition-all duration-700 ease-out"
                        :class="active === 1 ? 'opacity-100 translate-x-0 scale-100 z-30' : 'opacity-0 translate-x-10 scale-95 z-0'">
                        <div class="p-10 text-white shadow-2xl rounded-[2rem] h-full relative overflow-hidden card-3d flex flex-col justify-center border-0"
                             style="background: linear-gradient(to bottom right, var(--brand-pink), #f472b6);">
                            <div class="absolute text-6xl top-20 right-10 opacity-30 animate-bounce" style="animation-duration: 4s">🚆</div>
                            <div class="absolute text-5xl bottom-20 left-10 opacity-30 animate-pulse">🔧</div>

                            <span class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">NEW BATCH</span>
                            <h3 class="relative z-10 mb-4 text-4xl font-bold">RRB ALP 2025</h3>
                            <p class="relative z-10 max-w-xs mb-8 text-lg text-pink-100">Complete Technical + Non-Tech coverage.</p>
                            <button class="relative z-10 px-6 py-3 font-bold transition bg-white shadow-lg rounded-xl w-fit" style="color: var(--brand-pink);">Enroll Now</button>
                        </div>
                    </div>
                    <div class="absolute inset-0 transition-all duration-700 ease-out"
                        :class="active === 2 ? 'opacity-100 translate-x-0 scale-100 z-30' : 'opacity-0 translate-x-10 scale-95 z-0'">
                        <div class="p-10 text-white shadow-2xl rounded-[2rem] h-full relative overflow-hidden card-3d flex flex-col justify-center border-0"
                             style="background: linear-gradient(to bottom right, var(--brand-green), #a3e635);">
                            <div class="absolute text-6xl top-10 right-20 opacity-30 animate-bounce" style="animation-duration: 2.5s">🏦</div>
                            <div class="absolute text-5xl bottom-10 right-10 opacity-30 animate-pulse">📊</div>

                            <span class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">ADMISSIONS OPEN</span>
                            <h3 class="relative z-10 mb-4 text-4xl font-bold">Banking Elite</h3>
                            <p class="relative z-10 max-w-xs mb-8 text-lg text-green-900">One Pass for SBI PO, IBPS & RBI Grade B.</p>
                            <button class="relative z-10 px-6 py-3 font-bold transition bg-white shadow-lg rounded-xl w-fit" style="color: var(--brand-green);">Get Started</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white" x-data="{ currentTab: 'Engineering' }">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-extrabold lg:text-4xl text-slate-900">Popular Mock Tests</h2>
                <p class="text-lg text-slate-500">Attempt free mock tests curated by experts.</p>
            </div>

            <div class="flex flex-wrap justify-center gap-2 mb-12">
                @foreach ($popularTabs as $tab)
                    <button @click="currentTab = '{{ $tab }}'"
                        class="px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300"
                        :class="currentTab === '{{ $tab }}' ? 'text-white shadow-lg shadow-blue-500/30 scale-105' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        :style="currentTab === '{{ $tab }}' ? 'background-color: var(--brand-blue);' : ''">
                        {{ $tab }}
                    </button>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 min-h-[400px]">
                @foreach ($mockTests['Engineering'] as $test)
                    <div x-show="currentTab === 'Engineering'" x-transition:enter="transition ease-out duration-300"
                        class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1">
                        <div class="relative flex-1 p-6">
                            <div class="absolute top-0 right-0 p-4 transition-opacity opacity-10 group-hover:opacity-20">
                                <svg class="w-16 h-16" style="color: var(--brand-blue);" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                                </svg>
                            </div>
                            <div class="flex gap-2 mb-3">
                                @foreach ($test['tags'] as $tag)
                                    <span class="px-2 py-1 text-xs font-bold tracking-wider uppercase rounded-md bg-blue-50" style="color: var(--brand-blue);">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <h3 class="mb-2 text-xl font-bold transition-colors text-slate-800" style="group-hover:color: var(--brand-blue);">{{ $test['title'] }}</h3>
                            <p class="mb-4 text-sm text-slate-500">{{ $test['subtitle'] }}</p>
                            <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                                <span class="flex items-center gap-1">60 Mins</span>
                                <span class="flex items-center gap-1">{{ $test['users'] }} Users</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                            <div class="text-lg font-bold text-slate-900">₹{{ $test['price'] }} <span class="text-xs font-normal line-through text-slate-400">₹{{ $test['price'] * 2 }}</span></div>
                            <button class="px-4 py-2 text-sm font-bold transition-all bg-white border rounded-lg shadow-sm hover:text-white"
                                    style="color: var(--brand-blue); border-color: var(--brand-blue); hover:background-color: var(--brand-blue);">Attempt Now</button>
                        </div>
                    </div>
                @endforeach

                @foreach ($mockTests['Civil Services'] as $test)
                    <div x-show="currentTab === 'Civil Services'" x-transition:enter="transition ease-out duration-300"
                        class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1">
                        <div class="relative flex-1 p-6">
                            <div class="flex gap-2 mb-3">
                                @foreach ($test['tags'] as $tag)
                                    <span class="px-2 py-1 text-xs font-bold tracking-wider text-orange-600 uppercase rounded-md bg-orange-50">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <h3 class="mb-2 text-xl font-bold transition-colors text-slate-800 group-hover:text-orange-600">{{ $test['title'] }}</h3>
                            <p class="mb-4 text-sm text-slate-500">{{ $test['subtitle'] }}</p>
                        </div>
                        <div class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                            <div class="text-lg font-bold text-slate-900">₹{{ $test['price'] }}</div>
                            <button class="px-4 py-2 text-sm font-bold text-orange-600 transition-all bg-white border border-orange-600 rounded-lg shadow-sm hover:bg-orange-600 hover:text-white">Attempt Now</button>
                        </div>
                    </div>
                @endforeach

                <div x-show="!['Engineering', 'Civil Services'].includes(currentTab)" class="py-12 text-center col-span-full">
                    <p class="mb-4 text-slate-400">Showing top picks for <span x-text="currentTab" class="font-bold text-slate-600"></span></p>
                    <div class="grid grid-cols-1 gap-8 text-left md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($mockTests['default'] as $test)
                            <div class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1">
                                <div class="relative flex-1 p-6">
                                    <h3 class="mb-2 text-xl font-bold transition-colors text-slate-800" style="group-hover:color: var(--brand-pink);">{{ $test['title'] }}</h3>
                                    <p class="mb-4 text-sm text-slate-500">{{ $test['subtitle'] }}</p>
                                </div>
                                <div class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                                    <div class="text-lg font-bold text-slate-900">₹{{ $test['price'] }}</div>
                                    <button class="px-4 py-2 text-sm font-bold transition-all bg-white border rounded-lg shadow-sm hover:text-white"
                                            style="color: var(--brand-pink); border-color: var(--brand-pink); hover:background-color: var(--brand-pink);">Attempt Now</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <button class="px-8 py-3 font-bold transition bg-white border shadow-sm border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50">View All Test Series</button>
            </div>
        </div>
    </section>

    <section class="px-4 py-12">
        <div class="mx-auto max-w-7xl">
            <div class="relative p-8 overflow-hidden text-white shadow-2xl rounded-3xl md:p-12"
                 style="background: linear-gradient(to right, #0f172a, #1e293b);">
                <div class="absolute top-0 right-0 -mt-20 -mr-20 rounded-full w-96 h-96 opacity-20 blur-3xl" style="background-color: var(--brand-blue);"></div>

                <div class="relative z-10 grid items-center gap-8 md:grid-cols-2">
                    <div>
                        <div class="inline-block px-3 py-1 mb-4 text-xs font-bold text-white transform rounded-md -rotate-2 bg-gradient-to-r from-amber-400 to-orange-500">PREMIUM</div>
                        <h2 class="mb-4 text-3xl font-extrabold md:text-4xl">Enroll in Test Series for <span style="color: var(--brand-sky);">670+ exams</span></h2>
                        <p class="mb-8 text-lg text-slate-300">Get unlimited access to the most relevant Mock Tests on India's Structured Online Test series platform.</p>
                        <button class="px-8 py-3 font-bold text-white transition-all shadow-lg rounded-xl hover:scale-105"
                                style="background-color: var(--brand-blue);">Explore Exam Babu Pass</button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">🏆</div>
                            <div class="text-sm font-bold">All India Rank</div>
                        </div>
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">📝</div>
                            <div class="text-sm font-bold">Latest Patterns</div>
                        </div>
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">📊</div>
                            <div class="text-sm font-bold">In-depth Analysis</div>
                        </div>
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">🗣️</div>
                            <div class="text-sm font-bold">Multilingual</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative py-20 bg-slate-50">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900">Popular Test Series</h2>
                    <p class="mt-2 text-slate-500">Attempt free tests from our most popular packages.</p>
                </div>
                <a href="#" class="items-center hidden gap-1 font-bold md:flex hover:underline" style="color: var(--brand-blue);">
                    Explore all Test Series <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">
                @foreach ($popularTestSeries as $series)
                    <div class="p-6 transition-all duration-300 bg-white border shadow-sm rounded-2xl hover:shadow-xl border-slate-100 card-3d group">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold transition-colors text-slate-800" style="group-hover:color: var(--brand-blue);">{{ $series['title'] }}</h3>
                                @if (isset($series['subtitle']))
                                    <p class="text-sm font-medium text-slate-500">{{ $series['subtitle'] }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 px-2 py-1 text-xs font-bold text-green-700 border border-green-100 rounded bg-green-50">
                                <span class="live-dot w-1.5 h-1.5 bg-green-500"></span> LIVE
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mb-6 text-xs font-semibold text-slate-500">
                            <span class="flex items-center gap-1">{{ $series['users'] }} Users</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>{{ $series['total_tests'] }} Tests</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-green-600">{{ $series['free_tests'] }} Free Tests</span>
                        </div>

                        <div class="mb-6">
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($series['languages'] as $lang)
                                    <span class="text-[10px] uppercase font-bold px-2 py-1 bg-slate-100 text-slate-500 rounded border border-slate-200">{{ $lang }}</span>
                                @endforeach
                            </div>
                            <div class="space-y-2">
                                @foreach ($series['features'] as $feature)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 shrink-0" style="color: var(--brand-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $feature }}
                                    </div>
                                @endforeach
                                <div class="pl-6 text-xs font-bold cursor-pointer hover:underline" style="color: var(--brand-blue);">{{ $series['more_count'] }}</div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button class="w-full py-3 text-sm font-bold transition-all border shadow-sm rounded-xl hover:text-white"
                                    style="color: var(--brand-blue); border-color: var(--brand-blue); hover:background-color: var(--brand-blue);">View Test Series</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="relative py-20 overflow-hidden bg-slate-50">
        <div class="relative z-10 px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
            <h2 class="mb-6 text-3xl font-extrabold text-slate-900 md:text-4xl">Don't just take our word for it,<br>our results speak for themselves.</h2>
            <p class="max-w-2xl mx-auto mb-16 text-lg text-slate-500">We are proud to have partnered with lakhs of students in securing their dream job.</p>

            <div class="grid grid-cols-2 gap-6 md:grid-cols-5">
                @foreach ($stats as $stat)
                    <div class="p-6 transition-all duration-300 bg-white border shadow-sm border-slate-100 rounded-2xl stats-card-light hover:shadow-xl hover:-translate-y-2">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 text-2xl rounded-full {{ $stat['bg'] }} stats-icon transition-transform duration-300">
                            {{ $stat['icon'] }}
                        </div>
                        <div class="mb-1 text-2xl font-extrabold md:text-3xl {{ $stat['color'] }}">
                            {{ $stat['count'] }}</div>
                        <div class="text-xs font-bold tracking-wider uppercase text-slate-400">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-extrabold text-slate-900">Why Exam Babu?</h2>
                <p class="mt-2 text-slate-500">The smart way to prepare for government exams.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-4">
                <div class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-blue-100 rounded-2xl group-hover:scale-110">🎯</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Exam Oriented</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Content designed purely based on latest exam patterns and syllabus.</p>
                </div>
                <div class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-green-100 rounded-2xl group-hover:scale-110">📊</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Smart Analytics</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Get detailed report cards, strong/weak area analysis after every test.</p>
                </div>
                <div class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-purple-100 rounded-2xl group-hover:scale-110">🗣️</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Bilingual</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Switch between English and Hindi (or Marathi) anytime during the test.</p>
                </div>
                <div class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-orange-100 rounded-2xl group-hover:scale-110">💸</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Affordable</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Premium quality education at the most affordable prices in India.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 border-t bg-slate-50 border-slate-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-6">
                @foreach ($allTestSeries as $title => $items)
                    <div>
                        <h4 class="mb-4 text-sm font-bold tracking-wider uppercase text-slate-900">{{ $title }}</h4>
                        <ul class="space-y-2">
                            @foreach ($items as $item)
                                <li><a href="#" class="text-xs transition-colors text-slate-500 hover:underline" style="hover:color: var(--brand-blue);">{{ $item }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection
