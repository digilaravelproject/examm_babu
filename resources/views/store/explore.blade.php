@extends('layouts.site')

@section('content')

    {{--
        HERO SECTION
        Reduced top/bottom padding for a tighter look.
    --}}
    <section class="relative pt-28 pb-12 overflow-hidden"
        style="background: linear-gradient(135deg, var(--brand-blue) 0%, #055a91 100%);">

        {{-- Background Abstract Shapes --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[400px] h-[400px] transform translate-x-1/3 -translate-y-1/4 rounded-full opacity-20 blur-3xl"
                style="background-color: var(--brand-pink);"></div>
            <div class="absolute bottom-0 left-0 w-[300px] h-[300px] transform -translate-x-1/3 translate-y-1/3 rounded-full opacity-20 blur-3xl"
                style="background-color: var(--brand-sky);"></div>
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Breadcrumb (Compact) --}}
            <nav class="inline-flex mb-6 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20"
                aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2 text-xs font-medium text-white">
                    <li class="inline-flex items-center">
                        <a href="{{ route('welcome') }}"
                            class="hover:text-blue-200 transition-colors flex items-center gap-1.5">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                            </svg>
                            Home
                        </a>
                    </li>
                    <li><span class="text-white/50">/</span></li>
                    <li aria-current="page">
                        <span class="text-white font-semibold">{{ $category->name }}</span>
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="max-w-3xl">
                    {{-- Heading size slightly reduced --}}
                    <h1 class="mb-2 text-2xl font-bold tracking-tight text-white md:text-3xl lg:text-4xl">
                        {{ $category->name }}
                    </h1>

                    @if ($category->headline)
                        <p class="text-base font-medium text-blue-100 md:text-lg leading-relaxed">
                            {{ $category->headline }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT SECTION --}}
    <section class="py-10 bg-slate-50 relative">
        <div
            class="absolute top-0 left-0 right-0 h-12 bg-gradient-to-b from-slate-100/50 to-transparent pointer-events-none">
        </div>

        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Description Box (Compact padding) --}}
            @if ($category->description)
                <div class="mb-10 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
                        {!! $category->description !!}
                    </div>
                </div>
            @endif

            {{-- PLANS GRID --}}
            @if ($plans->count() > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div
                            class="group relative flex flex-col h-full bg-white border border-slate-200 rounded-2xl transition-all duration-300 hover:shadow-xl hover:border-blue-200 hover:-translate-y-1 overflow-hidden">

                            {{-- Best Seller Badge --}}
                            @if ($loop->first)
                                <div class="absolute top-0 right-0 z-10">
                                    <div class="text-[10px] font-bold text-white px-3 py-1 rounded-bl-lg shadow-sm"
                                        style="background: var(--brand-pink);">
                                        POPULAR
                                    </div>
                                </div>
                            @endif

                            {{-- Card Header --}}
                            <div class="p-6 pb-0">
                                <h3
                                    class="text-lg font-bold text-slate-900 group-hover:text-[var(--brand-blue)] transition-colors">
                                    {{ $plan->name }}
                                </h3>
                                <div class="mt-2 flex items-baseline">
                                    {{-- Price Text Reduced --}}
                                    <span class="text-3xl font-extrabold text-slate-900">
                                        {{ $siteSettings->currency_symbol ?? '₹' }}{{ $plan->price }}
                                    </span>
                                    <span class="ml-1.5 text-xs font-semibold text-slate-500 uppercase">/ Exam</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">One time payment, lifetime access.</p>
                            </div>

                            {{-- Divider --}}
                            <div class="w-full h-px bg-slate-100 my-4"></div>

                            {{-- Features List --}}
                            <div class="px-6 flex-1">
                                <ul role="list" class="space-y-3">
                                    @forelse($plan->features as $feature)
                                        <li class="flex items-start">
                                            <div
                                                class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center bg-lime-50 mt-0.5">
                                                <svg class="w-3.5 h-3.5" style="color: var(--brand-green);"
                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 16 12">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2.5"
                                                        d="M1 5.917 5.724 10.5 15 1.5" />
                                                </svg>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-slate-600">
                                                {{ $feature->code ?? $feature->name }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="text-xs text-slate-400 italic">Core features included.</li>
                                    @endforelse
                                </ul>
                            </div>

                            {{-- CTA Button --}}
                            <div class="p-6 mt-auto">
                                <a href="#"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 text-sm font-bold text-white rounded-lg transition-all duration-300 shadow-md hover:shadow-lg hover:scale-[1.01] focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    style="background: var(--brand-blue);">
                                    Buy Now
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div
                    class="flex flex-col items-center justify-center py-12 text-center bg-white rounded-2xl border border-dashed border-slate-300">
                    <div class="w-16 h-16 mb-4 rounded-full bg-blue-50 flex items-center justify-center">
                        <svg class="w-8 h-8 text-[var(--brand-blue)]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Coming Soon!</h3>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm">
                        Study material for <span class="font-semibold text-[var(--brand-blue)]">{{ $category->name }}</span>
                        is being prepared.
                    </p>
                    <a href="{{ route('welcome') }}"
                        class="mt-6 text-sm font-bold text-[var(--brand-blue)] hover:underline">
                        &larr; Back to Home
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- FEATURES BAR --}}
    <section class="py-8 bg-white border-t border-slate-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-1 gap-6 md:grid-cols-3 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
                <div class="px-4">
                    <div class="mx-auto flex items-center justify-center w-10 h-10 rounded-full mb-3 bg-blue-50 text-xl">
                        <span style="color: var(--brand-blue);">🎯</span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Latest Pattern</h4>
                    <p class="text-xs text-slate-500 mt-1">Updated for 2025</p>
                </div>
                <div class="px-4">
                    <div class="mx-auto flex items-center justify-center w-10 h-10 rounded-full mb-3 bg-green-50 text-xl">
                        <span style="color: var(--brand-green);">⚡</span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Instant Access</h4>
                    <p class="text-xs text-slate-500 mt-1">Start immediately</p>
                </div>
                <div class="px-4">
                    <div class="mx-auto flex items-center justify-center w-10 h-10 rounded-full mb-3 bg-pink-50 text-xl">
                        <span style="color: var(--brand-pink);">📱</span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Mobile Friendly</h4>
                    <p class="text-xs text-slate-500 mt-1">Learn anywhere</p>
                </div>
            </div>
        </div>
    </section>

@endsection
