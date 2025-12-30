@extends('layouts.site')

@section('content')

    {{-- HEADER / HERO SECTION (Compact but Detailed) --}}
    <section class="relative pt-24 pb-12 overflow-hidden"
        style="background: linear-gradient(135deg, var(--brand-blue, #1e40af) 0%, #0f172a 100%);">

        {{-- Background Effects --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-[250px] h-[250px] bg-indigo-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
        </div>

        <div class="relative px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Breadcrumb (Slim & Glassmorphic) --}}
            <nav class="inline-flex items-center gap-2 px-3 py-1.5 mb-5 rounded-full bg-white/10 backdrop-blur-md border border-white/10">
                <a href="{{ route('welcome') }}" class="text-xs font-medium text-blue-100 transition-colors hover:text-white">Home</a>
                <span class="text-white/40 text-[10px]">&bullet;</span>
                <span class="text-xs font-semibold tracking-wide text-white">{{ $category->name }}</span>
            </nav>

            <div class="max-w-4xl">
                <h1 class="mb-2 text-2xl font-extrabold tracking-tight text-white md:text-4xl">
                    {{ $category->name }}
                </h1>
                @if ($category->headline)
                    <p class="text-sm font-light leading-relaxed md:text-base text-blue-100/90">
                        {{ $category->headline }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <section class="relative py-10 bg-slate-50 min-h-[60vh]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Description Box (Restored but refined) --}}
            @if ($category->description)
                <div class="mb-10 overflow-hidden bg-white border shadow-sm border-slate-200 rounded-xl">
                    <div class="p-5 prose-sm prose md:p-6 prose-slate max-w-none text-slate-600">
                        {!! $category->description !!}
                    </div>
                </div>
            @endif

            {{-- PLANS GRID --}}
            @if ($plans->count() > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        @php
                            // Pricing Logic Restored
                            $price = $plan->price;
                            $hasDiscount = $plan->has_discount ?? false;
                            $discountPercentage = $plan->discount_percentage ?? 0;

                            $finalPrice = $price;
                            if ($hasDiscount && $discountPercentage > 0) {
                                $discountAmount = ($price * $discountPercentage) / 100;
                                $finalPrice = $price - $discountAmount;
                            }
                        @endphp

                        <div class="relative flex flex-col h-full overflow-hidden transition-all duration-300 bg-white border border-slate-200 rounded-2xl hover:shadow-xl hover:shadow-blue-900/5 hover:-translate-y-1 hover:border-blue-300 group">

                            {{-- Popular Badge (Restored) --}}
                            @if ($loop->first)
                                <div class="absolute top-0 right-0 z-10">
                                    <div class="text-[10px] font-bold text-white px-3 py-1 rounded-bl-xl shadow-sm"
                                         style="background: var(--brand-pink, #ec4899);">
                                        POPULAR
                                    </div>
                                </div>
                            @endif

                            {{-- Card Header --}}
                            <div class="p-5 pb-0">
                                <h3 class="text-lg font-bold transition-colors text-slate-800 group-hover:text-blue-600">
                                    {{ $plan->name }}
                                </h3>

                                {{-- Price Section --}}
                                <div class="flex flex-wrap items-end gap-2 mt-3">
                                    <span class="text-3xl font-extrabold leading-none text-slate-900">
                                        {{ $siteSettings->currency_symbol ?? '₹' }}{{ number_format($finalPrice, 0) }}
                                    </span>

                                    @if ($hasDiscount && $discountPercentage > 0)
                                        <div class="flex flex-col mb-0.5">
                                            <span class="text-xs line-through text-slate-400">
                                                {{ $siteSettings->currency_symbol ?? '₹' }}{{ number_format($price, 0) }}
                                            </span>
                                            <span class="text-[10px] font-bold text-green-600 leading-none">
                                                {{ $discountPercentage }}% OFF
                                            </span>
                                        </div>
                                    @else
                                        <span class="mb-1 text-xs font-medium text-slate-400">/ one time</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="w-full h-px my-4 bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                            {{-- Features List (Restored Details) --}}
                            <div class="flex-1 px-5">
                                <ul role="list" class="space-y-3">
                                    @forelse($plan->features as $feature)
                                        <li class="flex items-start">
                                            {{-- Green Check Icon Box --}}
                                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 flex items-center justify-center mt-0.5 border border-green-100">
                                                <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span class="ml-3 text-xs font-medium leading-snug md:text-sm text-slate-600">
                                                {{ $feature->code ?? $feature->name }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="text-xs italic text-slate-400">Basic features included.</li>
                                    @endforelse
                                </ul>
                            </div>

                            {{-- CTA Button (Restored Design) --}}
                            <div class="p-5 mt-auto">
                                <a href="{{ route('checkout', $plan->code) }}"
                                   class="group/btn w-full inline-flex justify-center items-center px-4 py-3 text-sm font-bold text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/25 active:scale-[0.98]"
                                   style="background: var(--brand-blue, #2563eb);">
                                    Buy Now
                                    <svg class="w-4 h-4 ml-2 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center py-16 text-center bg-white border border-dashed rounded-2xl border-slate-300">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 text-3xl rounded-full shadow-sm bg-slate-50">
                        ⏳
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Coming Soon!</h3>
                    <p class="max-w-xs mx-auto mt-2 text-sm text-slate-500">
                        We are currently updating the study material for <span class="font-semibold text-blue-600">{{ $category->name }}</span>.
                    </p>
                    <a href="{{ route('welcome') }}" class="mt-6 text-sm font-bold text-blue-600 hover:text-blue-700 hover:underline">
                        &larr; Back to Home
                    </a>
                </div>
            @endif

        </div>
    </section>

    {{-- FEATURES BAR (Restored Bottom Section but Compact) --}}
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
