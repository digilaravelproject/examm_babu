@extends('layouts.site')

@section('content')
    {{-- <section class="relative z-10 px-4 pt-32 pb-12 sm:px-6 lg:px-8">
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
    </section> --}}

    <section class="relative z-10 px-4 pt-32 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-center gap-12 lg:grid-cols-2">

                {{-- Left Side (Static for now, can be settings) --}}
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
                        <span class="text-transparent bg-clip-text"
                            style="background-image: linear-gradient(to right, var(--brand-blue), var(--brand-sky));">
                            Dream Job
                        </span>
                    </h1>

                    <p class="max-w-lg text-xl font-medium leading-relaxed text-slate-600" x-show="show"
                        x-transition:enter="transition ease-out duration-1000 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Join <b>2 Crore+ students</b> preparing for SSC, Banking, Railways & Engineering exams with India's
                        best Super Teachers.
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

                {{-- Right Side (Dynamic Slider) --}}
                <div class="hidden lg:block h-[450px] relative w-full" x-data="{ active: 0, count: {{ $heroSlides->count() }} }" x-init="setInterval(() => active = (active + 1) % count, 3500)">

                    @foreach ($heroSlides as $index => $slide)
                        <div class="absolute inset-0 transition-all duration-700 ease-out"
                            :class="active === {{ $index }} ? 'opacity-100 translate-x-0 scale-100 z-30' :
                                'opacity-0 translate-x-10 scale-95 z-0'">

                            {{-- Card Content --}}
                            <div class="p-10 text-white shadow-2xl rounded-[2rem] h-full relative overflow-hidden card-3d flex flex-col justify-center border-0"
                                style="background: linear-gradient(to bottom right, {{ $slide->bg_gradient_start }}, {{ $slide->bg_gradient_end }});">

                                {{-- Floating Icons --}}
                                <div class="absolute text-6xl top-10 right-10 opacity-30 animate-bounce"
                                    style="animation-duration: 3s">
                                    {{ $slide->icon_top }}
                                </div>
                                <div class="absolute text-5xl bottom-10 right-20 opacity-30 animate-pulse">
                                    {{ $slide->icon_bottom }}
                                </div>

                                {{-- Text Content --}}
                                <span
                                    class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">
                                    {{ $slide->badge_text }}
                                </span>

                                <h3 class="relative z-10 mb-4 text-4xl font-bold">
                                    {{ $slide->title }}
                                </h3>

                                <p class="relative z-10 max-w-xs mb-8 text-lg text-indigo-100 opacity-90">
                                    {{ $slide->description }}
                                </p>

                                <a href="{{ $slide->button_link }}"
                                    class="relative z-10 px-6 py-3 font-bold transition bg-white shadow-lg rounded-xl w-fit hover:scale-105 active:scale-95"
                                    style="color: {{ $slide->theme_color }};">
                                    {{ $slide->button_text }}
                                </a>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </section>

    @if (isset($categories) && $categories->isNotEmpty())
        @include('store.partials.home.popular_tests', [
            'categories' => $categories,
            'defaultTab' => $defaultTab,
        ])
    @endif

    {{-- <section class="px-4 py-12">
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
    </section> --}}

    {{-- <section class="relative py-20 bg-slate-50">
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
    </section> --}}

    <section class="relative py-20 overflow-hidden bg-slate-50">
        <div class="relative z-10 px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">

            {{-- Heading --}}
            <h2 class="mb-6 text-3xl font-extrabold text-slate-900 md:text-4xl">
                Don't just take our word for it,<br>our results speak for themselves.
            </h2>
            <p class="max-w-2xl mx-auto mb-16 text-lg text-slate-500">
                We are proud to have partnered with lakhs of students in securing their dream job.
            </p>

            {{-- Dynamic Stats Grid --}}
            <div class="grid grid-cols-2 gap-6 md:grid-cols-5">
                @foreach ($stats as $stat)
                    <div
                        class="p-6 transition-all duration-300 bg-white border shadow-sm border-slate-100 rounded-2xl stats-card-light hover:shadow-xl hover:-translate-y-2">

                        {{-- Icon with Dynamic Background Class --}}
                        <div
                            class="flex items-center justify-center w-12 h-12 mx-auto mb-4 text-2xl rounded-full {{ $stat->bg_class }} stats-icon transition-transform duration-300">
                            {{ $stat->icon }}
                        </div>

                        {{-- Count with Dynamic Text Color Class --}}
                        <div class="mb-1 text-2xl font-extrabold md:text-3xl {{ $stat->text_class }}">
                            {{ $stat->count }}
                        </div>

                        {{-- Label --}}
                        <div class="text-xs font-bold tracking-wider uppercase text-slate-400">
                            {{ $stat->label }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    {{-- <section class="py-20 bg-white">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-extrabold text-slate-900">Why Exam Babu?</h2>
                <p class="mt-2 text-slate-500">The smart way to prepare for government exams.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-4">
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-blue-100 rounded-2xl group-hover:scale-110">
                        🎯</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Exam Oriented</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Content designed purely based on latest exam patterns
                        and syllabus.</p>
                </div>
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-green-100 rounded-2xl group-hover:scale-110">
                        📊</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Smart Analytics</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Get detailed report cards, strong/weak area analysis
                        after every test.</p>
                </div>
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-purple-100 rounded-2xl group-hover:scale-110">
                        🗣️</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Bilingual</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Switch between English and Hindi (or Marathi) anytime
                        during the test.</p>
                </div>
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-orange-100 rounded-2xl group-hover:scale-110">
                        💸</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Affordable</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Premium quality education at the most affordable
                        prices in India.</p>
                </div>
            </div>
        </div>
    </section> --}}
    <section class="py-20 bg-white">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-extrabold text-slate-900">Why Exam Babu?</h2>
                <p class="mt-2 text-slate-500">The smart way to prepare for government exams.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-4">
                @foreach ($features as $feature)
                    <div
                        class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                        {{-- Dynamic Icon & Background Color --}}
                        <div
                            class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform {{ $feature->bg_class }} rounded-2xl group-hover:scale-110">
                            {{ $feature->icon }}
                        </div>

                        {{-- Dynamic Title --}}
                        <h3 class="mb-2 text-xl font-bold text-slate-900">{{ $feature->title }}</h3>

                        {{-- Dynamic Description --}}
                        <p class="text-sm leading-relaxed text-slate-500">
                            {{ $feature->description }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    {{-- <section class="py-16 border-t bg-slate-50 border-slate-100">
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
    </section> --}}
@endsection
