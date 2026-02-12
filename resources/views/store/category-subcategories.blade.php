@extends('layouts.site')

@section('content')

    {{-- Header Section --}}
    <section class="relative overflow-hidden pt-28 pb-14 bg-slate-900">

        {{-- Background Effects --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-purple-500/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
        </div>

        <div class="relative z-10 px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">

            {{-- ULTRA COMPACT BREADCRUMB --}}
            <nav class="inline-flex items-center gap-1.5 px-3 py-1 mb-6 text-[11px] font-semibold uppercase tracking-wide rounded-full bg-white/5 border border-white/10 text-slate-400 backdrop-blur-md">
                <a href="{{ route('welcome') }}" class="transition-colors hover:text-white">Home</a>
                <span class="text-slate-600">/</span>
                <span class="text-blue-400">{{ $parentCategory->name }}</span>
            </nav>

            <h1 class="mb-4 text-3xl font-extrabold tracking-tight text-white md:text-5xl">
                {{ $parentCategory->name }}
            </h1>

            <p class="max-w-xl mx-auto text-base font-light leading-relaxed text-slate-400">
                Select a category below to access premium mock tests and study materials.
            </p>
        </div>
    </section>

    {{-- Main Content Section --}}
    <section class="py-12 bg-slate-50 min-h-[50vh]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if($subCategories->count() > 0)
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($subCategories as $subCategory)
                        <a href="{{ route('explore', $subCategory->slug) }}"
                           class="relative flex flex-col h-full overflow-hidden transition-all duration-300 bg-white border group border-slate-200/60 rounded-xl hover:shadow-xl hover:shadow-blue-900/5 hover:-translate-y-1 hover:border-blue-200">

                            {{-- Top Active Line (Hover Effect) --}}
                            <div class="absolute top-0 left-0 w-full h-1 transition-transform duration-300 transform scale-x-0 bg-gradient-to-r from-blue-500 to-cyan-400 group-hover:scale-x-100"></div>

                            <div class="flex flex-col h-full p-5">
                                {{-- Card Header: Icon & Name --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center justify-center w-12 h-12 text-lg font-bold text-white transition-transform duration-300 rounded-lg shadow-md group-hover:scale-110"
                                         style="background: linear-gradient(135deg, var(--brand-blue, #3b82f6), var(--brand-sky, #0ea5e9));">
                                        {{ substr($subCategory->name, 0, 1) }}
                                    </div>

                                    {{-- Subtle Arrow Icon --}}
                                    <div class="flex items-center justify-center w-8 h-8 transition-all duration-300 rounded-full bg-slate-50 text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-500">
                                        <svg class="w-4 h-4 transform group-hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>

                                {{-- Title --}}
                                <h3 class="mb-2 text-lg font-bold transition-colors text-slate-800 group-hover:text-blue-600 line-clamp-1">
                                    {{ $subCategory->name }}
                                </h3>

                                {{-- Description (Restored Details) --}}
                                <div class="flex-1">
                                    @if($subCategory->headline)
                                        <p class="text-xs leading-relaxed text-slate-500 line-clamp-2">
                                            {{ $subCategory->headline }}
                                        </p>
                                    @else
                                        <p class="text-xs italic text-slate-400">
                                            Comprehensive test series & notes available.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer Section (Restored & Compacted) --}}
                            <div class="flex items-center justify-between px-5 py-3 text-xs font-semibold transition-colors border-t border-slate-100 bg-slate-50/50 group-hover:bg-blue-50/30">
                                <span class="text-slate-400 group-hover:text-slate-600">View Details</span>
                                <span class="flex items-center gap-1 text-blue-500 transition-transform group-hover:translate-x-1">
                                    Explore
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </span>
                            </div>

                        </a>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 text-3xl rounded-full shadow-inner bg-slate-100 animate-pulse">
                        📂
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No Exams Found</h3>
                    <p class="max-w-xs mx-auto mt-2 mb-6 text-sm text-slate-500">
                        Content for this category is being updated. Please check back later.
                    </p>
                    <a href="{{ route('welcome') }}"
                       class="px-5 py-2.5 text-xs font-bold text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors shadow-lg shadow-slate-200">
                        Back to Home
                    </a>
                </div>
            @endif

        </div>
    </section>

@endsection
