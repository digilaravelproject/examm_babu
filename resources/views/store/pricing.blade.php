@php
    $lightNavbar = true;
@endphp
@extends('layouts.site')

@section('content')

    <section class="relative pt-24 pb-16 overflow-hidden"
        style="background: linear-gradient(135deg, var(--brand-blue, #1e40af) 0%, #0f172a 100%);">

        {{-- Background Effects --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-indigo-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
        </div>

        <div class="relative px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-yellow-300 mb-4 tracking-wider uppercase">
                Premium Access
            </span>
            <h1 class="mb-4 text-3xl font-extrabold tracking-tight text-white md:text-5xl">
                Choose Your Success Plan
            </h1>
            <p class="max-w-2xl mx-auto text-sm font-light leading-relaxed md:text-base text-blue-100/90">
                Unlimited access to mock tests, quizzes. Start your journey today.
            </p>
        </div>
    </section>

    {{--
        2. MAIN CONTENT AREA
        Contains Tabs and the Reference Card Design
    --}}
    <section class="relative py-12 bg-slate-50 min-h-[80vh]" x-data="{ activeTab: '{{ $selectedCategory }}' }">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{--
                TABS (Sleek Style)
                To switch between exams/categories
            --}}
            <div class="flex justify-center mb-10">
                <div class="inline-flex p-1.5 bg-white border border-slate-200 rounded-full shadow-sm overflow-x-auto max-w-full custom-scrollbar">
                    @foreach($categories as $category)
                        <button @click="activeTab = '{{ $category->code }}'"
                            class="px-5 py-2 text-sm font-bold rounded-full whitespace-nowrap transition-all duration-200"
                            :class="activeTab === '{{ $category->code }}'
                                ? 'bg-slate-900 text-white shadow-md'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- PLANS GRID WRAPPER --}}
            <div class="min-h-[400px]">
                @foreach($categories as $category)
                    <div x-show="activeTab === '{{ $category->code }}'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="display: none;">

                        @if($category->plans->count() > 0)
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                @foreach($category->plans as $plan)

                                    {{-- PRICE CALCULATION LOGIC --}}
                                    @php
                                        $originalPrice = (float) $plan->price;
                                        $discountPercent = (int) $plan->discount_percentage;
                                        $hasDiscount = ($plan->has_discount && $discountPercent > 0);
                                        $sellingPrice = $originalPrice;
                                        $savings = 0;

                                        if ($hasDiscount) {
                                            $savings = ($originalPrice * $discountPercent) / 100;
                                            $sellingPrice = $originalPrice - $savings;
                                        }
                                        $sellingPrice = round($sellingPrice);
                                        $savings = round($savings);
                                    @endphp

                                    {{--
                                        THE EXACT CARD UI FROM YOUR REFERENCE
                                    --}}
                                    <div class="relative flex flex-col h-full overflow-hidden transition-all duration-300 bg-white border border-slate-200 rounded-2xl hover:shadow-xl hover:shadow-blue-900/5 hover:-translate-y-1 hover:border-blue-300 group">

                                        {{-- Popular Badge --}}
                                        @if ($plan->is_popular || $loop->first)
                                            <div class="absolute top-0 right-0 z-10">
                                                <div class="text-[10px] font-bold text-white px-3 py-1 rounded-bl-xl shadow-sm"
                                                    style="background: var(--brand-pink, #ec4899);">
                                                    POPULAR
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Card Content --}}
                                        <div class="p-5 pb-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-0.5 text-[10px] font-bold text-slate-500 bg-slate-100 rounded-md border border-slate-200">
                                                    {{ $plan->duration }} Days
                                                </span>
                                                @if($hasDiscount)
                                                    <span class="px-2 py-0.5 text-[10px] font-bold text-white bg-green-600 rounded-md animate-pulse">
                                                        {{ $discountPercent }}% OFF
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="text-lg font-bold transition-colors text-slate-800 group-hover:text-blue-600 line-clamp-2 min-h-[3.5rem]">
                                                {{ $plan->name }}
                                            </h3>

                                            {{-- Price --}}
                                            <div class="mt-4">
                                                <div class="flex items-end gap-2">
                                                    <span class="text-3xl font-extrabold leading-none text-slate-900">
                                                        {{ $siteSettings->currency_symbol ?? '₹' }}{{ number_format($sellingPrice, 0) }}
                                                    </span>
                                                    @if ($hasDiscount)
                                                        <span class="mb-1 text-sm font-medium line-through text-slate-400">
                                                            {{ $siteSettings->currency_symbol ?? '₹' }}{{ number_format($originalPrice, 0) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center justify-between mt-1">
                                                    @if ($hasDiscount)
                                                        <p class="text-xs font-bold text-green-600">
                                                            You Save {{ $siteSettings->currency_symbol ?? '₹' }}{{ number_format($savings, 0) }}
                                                        </p>
                                                    @else
                                                        <p class="text-xs font-medium text-slate-400">Best value price</p>
                                                    @endif
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">+ GST</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Divider --}}
                                        <div class="w-full h-px my-4 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                                        {{-- Features List --}}
                                        <div class="flex-1 px-5">
                                            <ul role="list" class="space-y-3">
                                                @forelse($plan->features->take(4) as $feature)
                                                    <li class="flex items-start">
                                                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 flex items-center justify-center mt-0.5 border border-green-100">
                                                            <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                        <span class="ml-3 text-xs font-medium leading-snug md:text-sm text-slate-600 line-clamp-1">
                                                            {{ $feature->name }}
                                                        </span>
                                                    </li>
                                                @empty
                                                    <li class="text-xs italic text-slate-400">Comprehensive.</li>
                                                @endforelse
                                            </ul>
                                        </div>

                                        {{-- CTA BUTTON (With Admin Logic) --}}
                                        <div class="p-5 mt-auto">
                                            @if (auth()->check() && auth()->user()->hasRole('admin'))
                                                {{-- ADMIN VIEW --}}
                                                <button type="button" disabled
                                                    class="w-full inline-flex justify-center items-center px-4 py-3 text-sm font-bold text-gray-400 bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    Admin View Only
                                                </button>
                                            @else
                                                {{-- STUDENT VIEW --}}
                                                <a href="{{ route('checkout', $plan->code) }}"
                                                   class="group/btn w-full inline-flex justify-center items-center px-4 py-3 text-sm font-bold text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/25 active:scale-[0.98]"
                                                   style="background: var(--brand-blue, #2563eb);">
                                                    Attempt Now
                                                    <svg class="w-4 h-4 ml-2 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Empty State (Exactly from reference) --}}
                            <div class="flex flex-col items-center justify-center py-16 text-center bg-white border border-dashed rounded-2xl border-slate-300">
                                <div class="flex items-center justify-center w-16 h-16 mb-4 text-3xl rounded-full shadow-sm bg-slate-50">
                                    ⏳
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">Coming Soon!</h3>
                                <p class="max-w-xs mx-auto mt-2 text-sm text-slate-500">
                                    We are currently updating plans for <span class="font-semibold text-blue-600">{{ $category->name }}</span>.
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    {{--
        3. FEATURES BAR (Exactly from reference)
    --}}
    <section class="py-6 bg-white border-t border-slate-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 text-center md:grid-cols-3 md:divide-x divide-slate-100">
                <div class="flex items-center justify-center gap-4 md:flex-col md:gap-2">
                    <div class="flex items-center justify-center w-10 h-10 text-lg rounded-full shadow-sm bg-blue-50">
                        <span class="text-blue-500">🎯</span>
                    </div>
                    <div class="text-left md:text-center">
                        <h4 class="text-sm font-bold text-slate-900">Latest Pattern</h4>
                        <p class="text-xs text-slate-500">Updated Syllabus</p>
                    </div>
                </div>
                <div class="flex items-center justify-center gap-4 md:flex-col md:gap-2">
                    <div class="flex items-center justify-center w-10 h-10 text-lg rounded-full shadow-sm bg-green-50">
                        <span class="text-green-500">⚡</span>
                    </div>
                    <div class="text-left md:text-center">
                        <h4 class="text-sm font-bold text-slate-900">Instant Access</h4>
                        <p class="text-xs text-slate-500">Start Learning Now</p>
                    </div>
                </div>
                <div class="flex items-center justify-center gap-4 md:flex-col md:gap-2">
                    <div class="flex items-center justify-center w-10 h-10 text-lg rounded-full shadow-sm bg-pink-50">
                        <span class="text-pink-500">📱</span>
                    </div>
                    <div class="text-left md:text-center">
                        <h4 class="text-sm font-bold text-slate-900">Mobile Friendly</h4>
                        <p class="text-xs text-slate-500">Study Anywhere</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
