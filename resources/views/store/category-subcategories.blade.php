@extends('layouts.site')

@section('content')

    <section class="relative pt-32 pb-24 overflow-hidden"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-pink-500/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
        </div>

        <div class="relative px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 text-center">

            <nav class="inline-flex items-center px-4 py-2 mb-8 space-x-2 text-sm font-medium rounded-full bg-white/5 backdrop-blur-md border border-white/10 text-slate-300">
                <a href="{{ route('welcome') }}" class="hover:text-white transition-colors">Home</a>
                <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-white" style="color: var(--brand-sky);">{{ $parentCategory->name }}</span>
            </nav>

            <h1 class="mb-6 text-4xl font-extrabold tracking-tight text-white md:text-6xl">
                <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, white, #cbd5e1);">
                    {{ $parentCategory->name }}
                </span>
            </h1>

            <p class="max-w-2xl mx-auto text-lg text-slate-400">
                Choose from our wide range of exam categories. Prepare with the best mock tests and study material designed by experts.
            </p>
        </div>
    </section>

    <section class="py-20 bg-slate-50 min-h-[60vh]">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if($subCategories->count() > 0)
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($subCategories as $subCategory)
                        <a href="{{ route('explore', $subCategory->slug) }}"
                           class="relative flex flex-col p-6 h-full transition-all duration-300 bg-white border border-slate-100 rounded-2xl group hover:shadow-xl hover:-translate-y-2 overflow-hidden">

                            <div class="absolute top-0 left-0 w-full h-1 transition-all duration-300 transform scale-x-0 group-hover:scale-x-100" style="background-color: var(--brand-blue);"></div>

                            <div class="flex items-start justify-between mb-6">
                                <div class="flex items-center justify-center w-14 h-14 rounded-2xl text-xl font-bold text-white shadow-lg transition-transform group-hover:scale-110 group-hover:rotate-3"
                                     style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-sky));">
                                    {{ substr($subCategory->name, 0, 1) }}
                                </div>

                                <div class="p-2 rounded-full bg-slate-50 group-hover:bg-blue-50 transition-colors">
                                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transform group-hover:rotate-45 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1">
                                <h3 class="mb-3 text-xl font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                    {{ $subCategory->name }}
                                </h3>

                                @if($subCategory->headline)
                                    <p class="text-sm leading-relaxed text-slate-500 line-clamp-2">
                                        {{ $subCategory->headline }}
                                    </p>
                                @else
                                    <p class="text-sm text-slate-400"> comprehensive test series & study notes.</p>
                                @endif
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-50 flex items-center justify-between text-sm font-semibold">
                                <span class="text-slate-400 group-hover:text-slate-600 transition-colors">View Details</span>
                                <span class="group-hover:translate-x-1 transition-transform duration-300" style="color: var(--brand-blue);">Explore &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="relative mb-6">
                        <div class="absolute inset-0 bg-blue-100 rounded-full animate-ping opacity-75"></div>
                        <div class="relative flex items-center justify-center w-24 h-24 bg-white rounded-full shadow-lg border border-slate-100 text-5xl">
                            🔍
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900">No Exams Found</h3>
                    <p class="mt-3 text-slate-500 max-w-md">
                        We couldn't find any exams under this category yet. We are working on adding new content.
                    </p>
                    <a href="{{ route('welcome') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 mt-8 text-sm font-bold text-white transition-transform rounded-xl hover:-translate-y-1 shadow-lg shadow-blue-500/30"
                       style="background: linear-gradient(to right, var(--brand-blue), #0ea5e9);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Back to Home
                    </a>
                </div>
            @endif

        </div>
    </section>

@endsection
