@php
    $lightNavbar = true;
@endphp

@extends('layouts.site')

@section('content')

    {{-- ================= HERO SECTION ================= --}}
    <section class="relative pt-24 pb-16 overflow-hidden"
        style="background: linear-gradient(135deg, var(--brand-blue, #1e40af) 0%, #0f172a 100%);">

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute top-0 right-0 w-[400px] h-[400px] bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3">
            </div>
            <div
                class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-indigo-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3">
            </div>
        </div>

        <div class="relative px-4 mx-auto max-w-7xl text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-white/10 text-xs font-bold text-yellow-300 mb-4 uppercase border border-white/20">
                {{ $subCategory->name }}
            </span>

            <h1 class="mb-3 text-3xl font-extrabold text-white md:text-5xl">
                {{ $microCategory ? $microCategory->name : 'Exam Details' }}
            </h1>

            <p class="text-blue-100 text-sm md:text-base max-w-2xl mx-auto">
                Unlock the complete test series and boost your preparation today.
            </p>
        </div>
    </section>

    {{-- ================= MAIN CONTENT ================= --}}
    <section class="relative py-12 bg-slate-50 min-h-[80vh]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="grid gap-8 lg:grid-cols-3">

                {{--
                LEFT COLUMN (Span 2):
                Subjects in CARD GRID Layout
            --}}
                <div class="lg:col-span-2">

                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <span>📚</span> Included Subjects / Tests
                        </h2>
                        <span class="text-sm text-slate-500">{{ $exams->count() }} Subjects</span>
                    </div>

                    @if ($exams->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($exams as $exam)
                                <div
                                    class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">

                                    {{-- Card Header: Icon & Status --}}
                                    <div class="flex items-start justify-between mb-4">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                            📝
                                        </div>
                                        <span
                                            class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                                            Included
                                        </span>
                                    </div>

                                    {{-- Title --}}
                                    <h3
                                        class="text-lg font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors line-clamp-1">
                                        {{ $exam->title }}
                                    </h3>

                                    {{-- Description --}}
                                    <p class="text-sm text-slate-500 line-clamp-2 mb-4 h-10">
                                        {{ $exam->description ?? 'Topic-wise questions and mock tests included.' }}
                                    </p>

                                    {{-- Divider --}}
                                    <div class="w-full h-px bg-slate-100 mb-4"></div>

                                    {{-- Meta Info --}}
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $exam->duration ?? '60' }} Mins
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ rand(20, 50) }} Ques
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-2xl p-10 text-center border border-dashed border-slate-300">
                            <div
                                class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-3xl">
                                📂</div>
                            <h3 class="text-slate-900 font-bold text-lg">No Subjects Found</h3>
                            <p class="text-slate-500 text-sm">Content is being uploaded for this exam.</p>
                        </div>
                    @endif
                </div>

                {{--
                RIGHT COLUMN (Span 1):
                Pricing / Payment Card (Sticky)
            --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">

                        @if ($microCategory && $microCategory->plans->isNotEmpty())
                            @foreach ($microCategory->plans as $plan)
                                @php
                                    $originalPrice = (float) $plan->price;
                                    $discountPercent = (int) $plan->discount_percentage;
                                    $hasDiscount = $plan->has_discount && $discountPercent > 0;
                                    $sellingPrice = $hasDiscount
                                        ? $originalPrice - ($originalPrice * $discountPercent) / 100
                                        : $originalPrice;
                                    $sellingPrice = round($sellingPrice);
                                    $savings = round($originalPrice - $sellingPrice);
                                @endphp

                                <div
                                    class="bg-white rounded-2xl shadow-xl border border-blue-100 overflow-hidden relative transition-transform hover:-translate-y-1">

                                    {{-- Popular Ribbon --}}
                                    @if ($plan->is_popular || $loop->first)
                                        <div class="absolute top-0 right-0">
                                            <div class="text-[10px] font-bold text-white px-3 py-1 rounded-bl-xl shadow-sm"
                                                style="background: var(--brand-pink, #ec4899);">
                                                POPULAR
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Title Header --}}
                                    <div class="p-6 pb-0">
                                        <span
                                            class="inline-block px-2 py-1 text-[10px] font-bold text-slate-500 bg-slate-100 rounded border border-slate-200 mb-2">
                                            {{ $plan->duration }} Days Validity
                                        </span>
                                        <h3 class="text-xl font-bold text-slate-900 leading-tight">
                                            {{ $plan->name }}
                                        </h3>
                                    </div>

                                    <div class="p-6 pt-4">
                                        {{-- Pricing --}}
                                        <div class="mb-6">
                                            <div class="flex items-center gap-2 items-baseline">
                                                <span class="text-4xl font-extrabold text-slate-900">
                                                    {{ $siteSettings->currency_symbol ?? '₹' }}{{ $sellingPrice }}
                                                </span>
                                                <span class="text-sm font-medium text-slate-500">
                                                    Best value price
                                                </span>
                                            </div>
                                            @if ($hasDiscount)
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-sm text-slate-400 line-through">
                                                        {{ $siteSettings->currency_symbol ?? '₹' }}{{ $originalPrice }}
                                                    </span>
                                                    <span class="text-xs font-bold text-green-600">
                                                        {{ $discountPercent }}% OFF
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Divider --}}
                                        <div class="w-full h-px bg-slate-100 mb-6"></div>

                                        {{-- FEATURES LIST (Updated: Line by Line List) --}}
                                        <ul class="space-y-3 mb-8">
                                            @if ($plan->features && $plan->features->count() > 0)
                                                @foreach ($plan->features as $feature)
                                                    <li class="flex items-start gap-3 text-sm text-slate-600">
                                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span class="leading-tight">{{ $feature->name }}</span>
                                                    </li>
                                                @endforeach
                                            @else
                                                {{-- Fallback Features if none in DB --}}
                                                <li class="flex items-start gap-3 text-sm text-slate-600">
                                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Comprehensive study material.</span>
                                                </li>
                                                <li class="flex items-start gap-3 text-sm text-slate-600">
                                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Unlimited Mock Tests</span>
                                                </li>
                                                <li class="flex items-start gap-3 text-sm text-slate-600">
                                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>In-depth Performance Analysis</span>
                                                </li>
                                            @endif
                                        </ul>

                                        {{-- Action Button --}}
                                        @if (auth()->check() && auth()->user()->hasRole('admin'))
                                            <button disabled
                                                class="w-full py-3.5 rounded-xl font-bold bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200">
                                                Admin Preview
                                            </button>
                                        @else
                                            <a href="{{ route('checkout', $plan->code) }}"
                                                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl font-bold text-white text-center shadow-lg shadow-blue-500/30 transition-all hover:shadow-blue-500/50 hover:-translate-y-0.5 active:translate-y-0"
                                                style="background: var(--brand-blue, #2563eb);">
                                                Attempt Now
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        @endif

                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- No Plan Available State --}}
                            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 text-center">
                                <div class="text-4xl mb-3">🚧</div>
                                <h3 class="font-bold text-slate-800">Registration Closed</h3>
                                <p class="text-sm text-slate-500 mt-1">Enrollment for this exam is currently unavailable.
                                </p>
                            </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection
