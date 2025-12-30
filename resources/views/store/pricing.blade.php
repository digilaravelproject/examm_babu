@extends('layouts.site')

@section('content')

    {{--
        HERO SECTION
        Updated Copy: More student-centric, Hinglish touch, and energetic.
    --}}
    <section class="relative pt-32 pb-24 overflow-hidden text-center"
             style="background: linear-gradient(135deg, var(--brand-blue) 0%, #055a91 100%);">

        {{-- Background Shapes --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] transform translate-x-1/3 -translate-y-1/4 rounded-full opacity-10 blur-3xl bg-white"></div>
            <div class="absolute bottom-0 left-0 w-[300px] h-[300px] transform -translate-x-1/3 translate-y-1/3 rounded-full opacity-10 blur-3xl"
                 style="background-color: var(--brand-pink);"></div>
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px;"></div>
        </div>

        <div class="relative px-4 mx-auto max-w-4xl sm:px-6 lg:px-8">
            <span class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-xs font-bold text-yellow-300 mb-4 tracking-wider uppercase">
                Pricing Plans
            </span>
            <h1 class="mb-4 text-4xl font-extrabold tracking-tight text-white md:text-5xl lg:text-6xl leading-tight">
                A small step today can lead to<br>
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, #fff, var(--brand-sky));"> a government job tomorrow. </span>
            </h1>
            {{-- <p class="max-w-2xl mx-auto text-lg text-blue-100 font-medium leading-relaxed">
                Apne exam ki tyari shuru karein bina budget ki chinta kiye. <br class="hidden md:block">Select your goal below and check our affordable plans.
            </p> --}}
        </div>
    </section>

    {{-- PRICING CONTENT SECTION --}}
    <section class="py-12 bg-slate-50 relative min-h-screen"
             x-data="{
                activeTab: '{{ $selectedCategory }}',
                activeName: '{{ $categories->firstWhere('code', $selectedCategory)->name ?? 'Select Exam Category' }}',
                openDropdown: false
             }">

        <div class="absolute top-0 left-0 right-0 h-24 bg-gradient-to-b from-slate-100/60 to-transparent pointer-events-none"></div>

        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 relative z-10">

            {{--
                CENTERED DROPDOWN SELECTOR
                Replaces the long sidebar list.
            --}}
            <div class="max-w-xl mx-auto mb-16">
                <label class="block text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">Select Your Exam Category</label>

                <div class="relative">
                    {{-- Dropdown Button --}}
                    <button @click="openDropdown = !openDropdown"
                            @click.away="openDropdown = false"
                            class="w-full bg-white border-2 border-slate-200 rounded-2xl py-4 px-6 flex items-center justify-between shadow-lg shadow-blue-900/5 hover:border-blue-400 transition-all duration-300 group">
                        <span class="text-lg font-bold text-slate-800" x-text="activeName"></span>
                        <div class="bg-blue-50 rounded-full p-2 group-hover:bg-blue-100 transition-colors">
                            <svg class="w-5 h-5 text-[var(--brand-blue)] transition-transform duration-300"
                                 :class="openDropdown ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Dropdown Menu (Scrollable for long lists) --}}
                    <div x-show="openDropdown"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50 max-h-80 overflow-y-auto custom-scrollbar"
                         style="display: none;">

                        <div class="p-2 space-y-1">
                            @foreach($categories as $category)
                                <button @click="activeTab = '{{ $category->code }}'; activeName = '{{ $category->name }}'; openDropdown = false"
                                        class="w-full text-left px-4 py-3 rounded-xl font-medium transition-colors flex items-center justify-between"
                                        :class="activeTab === '{{ $category->code }}'
                                            ? 'bg-blue-50 text-[var(--brand-blue)] font-bold'
                                            : 'text-slate-600 hover:bg-slate-50'">
                                    <span>{{ $category->name }}</span>
                                    <span x-show="activeTab === '{{ $category->code }}'" class="text-[var(--brand-blue)]">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- PLANS DISPLAY AREA --}}
            <div class="min-h-[400px]">
                @foreach($categories as $category)
                    <div x-show="activeTab === '{{ $category->code }}'"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;">

                        {{-- Category Title (Optional, just to confirm selection) --}}
                        <div class="text-center mb-10">
                            <h2 class="text-2xl font-bold text-slate-900">
                                Plans for <span class="text-[var(--brand-blue)] border-b-4 border-blue-100 px-2">{{ $category->name }}</span>
                            </h2>
                        </div>

                        @if($category->plans->count() > 0)
                            {{-- Grid changed to 3 columns since full width is available --}}
                            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
                                @foreach($category->plans as $plan)
                                    <div class="group relative flex flex-col h-full bg-white border border-slate-200 rounded-3xl transition-all duration-300 hover:shadow-2xl hover:border-blue-200 hover:-translate-y-2 overflow-hidden">

                                        {{-- Best Value Badge --}}
                                        @if($loop->last && $category->plans->count() > 1)
                                            <div class="absolute top-0 right-0 z-10">
                                                <div class="text-xs font-bold text-white px-4 py-1.5 rounded-bl-xl shadow-md"
                                                     style="background: var(--brand-pink);">
                                                    STUDENT CHOICE
                                                </div>
                                            </div>
                                        @endif

                                        <div class="p-8 pb-0">
                                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-[var(--brand-blue)] transition-colors">
                                                {{ $plan->name }}
                                            </h3>

                                            <div class="mt-4 flex items-end gap-2">
                                                <span class="text-4xl font-extrabold text-slate-900 leading-none">
                                                    {{ $siteSettings->currency_symbol ?? '₹' }}{{ $plan->price }}
                                                </span>
                                                @if($plan->price < ($plan->price * 1.5))
                                                    <div class="flex flex-col mb-1">
                                                        <span class="text-xs text-slate-400 line-through">
                                                            {{ $siteSettings->currency_symbol ?? '₹' }}{{ floor($plan->price * 1.5) }}
                                                        </span>
                                                        <span class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 rounded">
                                                            SAVE 33%
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <p class="mt-3 text-sm font-medium text-slate-500">Validity: Lifetime Access</p>
                                        </div>

                                        <div class="w-full h-px bg-slate-100 my-6"></div>

                                        <div class="px-8 flex-1">
                                            <ul class="space-y-4 mb-8">
                                                @forelse($plan->features as $feature)
                                                    <li class="flex items-start">
                                                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-lime-50 flex items-center justify-center mt-0.5">
                                                            <svg class="w-3.5 h-3.5" style="color: var(--brand-green);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                        <span class="ml-3 text-slate-600 text-sm font-medium">
                                                            {{ $feature->code ?? $feature->name }}
                                                        </span>
                                                    </li>
                                                @empty
                                                    <li class="text-slate-400 italic text-sm">Full test series access.</li>
                                                @endforelse
                                            </ul>
                                        </div>

                                        <div class="p-8 mt-auto pt-0">
                                            <a href="#"
                                               class="w-full inline-flex justify-center items-center px-6 py-4 text-base font-bold text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-[1.02] focus:ring-4 focus:ring-blue-100"
                                               style="background: var(--brand-blue);">
                                                Start Preparing
                                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Empty State --}}
                            <div class="max-w-md mx-auto p-10 text-center bg-white rounded-3xl border border-dashed border-slate-300">
                                <div class="w-20 h-20 mx-auto mb-6 bg-slate-50 rounded-full flex items-center justify-center animate-pulse">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800">Coming Soon</h3>
                                <p class="text-slate-500 mt-2">New batches for {{ $category->name }} are starting soon.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FEATURES SECTION --}}
    @if($features->count() > 0)
    <section class="py-16 bg-white border-t border-slate-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-[var(--brand-blue)] font-bold tracking-wider uppercase text-sm">Why Exam Babu?</span>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-900">Features that make you a Topper</h2>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                @foreach($features as $feature)
                    <div class="group p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:bg-white hover:border-blue-100 hover:shadow-xl transition-all duration-300">
                        <div class="w-14 h-14 mb-6 rounded-2xl flex items-center justify-center transition-colors bg-white shadow-sm group-hover:bg-[var(--brand-blue)] group-hover:text-white text-[var(--brand-blue)]">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">{{ $feature->name }}</h4>
                        <p class="text-slate-500 leading-relaxed">
                            Unlimited access to {{ strtolower($feature->name) }}. Designed by experts to match the latest exam pattern.
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

@endsection
