<section class="py-20 bg-white" x-data="{ currentTab: '{{ $defaultTab }}' }">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="mb-12 text-center">
            <h2 class="mb-4 text-3xl font-extrabold lg:text-4xl text-slate-900">Popular Mock Tests</h2>
            <p class="text-lg text-slate-500">Attempt free mock tests curated by experts.</p>
        </div>

        {{-- 1. DYNAMIC TABS (Categories) --}}
        <div class="flex flex-wrap justify-center gap-2 mb-12">
            @foreach ($categories as $category)
                <button @click="currentTab = '{{ $category->name }}'"
                    class="px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300"
                    :class="currentTab === '{{ $category->name }}' ? 'text-white shadow-lg shadow-blue-500/30 scale-105' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    :style="currentTab === '{{ $category->name }}' ? 'background-color: var(--brand-blue);' : ''">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        {{-- 2. DYNAMIC CONTENT GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 min-h-[400px]">

            @foreach ($categories as $category)
                {{-- Only show content if this Category Tab is active --}}
                <template x-if="currentTab === '{{ $category->name }}'">

                    {{-- Check if Category has ANY plans across all subcategories --}}
                    @if($category->subCategories->flatMap->plans->count() > 0)

                        {{-- Loop 1: SubCategories inside this Category --}}
                        @foreach ($category->subCategories as $subCategory)

                            {{-- Check if SubCategory has plans --}}
                            @if($subCategory->plans->isNotEmpty())

                                {{-- Loop 2: Plans inside this SubCategory --}}
                                @foreach ($subCategory->plans as $plan)
                                    <div class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1 animate-fade-in-up">

                                        <div class="relative flex-1 p-6">
                                            {{-- Background Icon --}}
                                            <div class="absolute top-0 right-0 p-4 transition-opacity opacity-10 group-hover:opacity-20">
                                                <svg class="w-16 h-16" style="color: var(--brand-blue);" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                                                </svg>
                                            </div>

                                            <div class="flex flex-wrap gap-2 mb-3">
                                                {{-- 🟢 BADGE: SubCategory Name (e.g. Delhi Police) --}}
                                                <span class="px-2 py-1 text-xs font-bold tracking-wider text-blue-700 uppercase rounded-md bg-blue-50">
                                                    {{ $subCategory->name }}
                                                </span>

                                                {{-- Discount Badge --}}
                                                @if($plan->has_discount)
                                                    <span class="px-2 py-1 text-xs font-bold tracking-wider text-green-600 uppercase rounded-md bg-green-50">OFFER</span>
                                                @endif
                                            </div>

                                            <h3 class="mb-2 text-xl font-bold transition-colors text-slate-800 line-clamp-2 group-hover:text-blue-600">
                                                {{ $plan->name }}
                                            </h3>

                                            <p class="mb-4 text-sm text-slate-500 line-clamp-1">
                                                {{ $plan->description ?? 'Comprehensive Test Series' }}
                                            </p>

                                            <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                                                <span class="flex items-center gap-1">⏱ {{ $plan->duration ?? 30 }} Days</span>
                                                {{-- Random Users Logic for demo feel --}}
                                                <span class="flex items-center gap-1">👥 {{ rand(100, 2000) }}+ Users</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                                            <div class="text-lg font-bold text-slate-900">
                                                @if($plan->price > 0)
                                                    ₹{{ $plan->price }}
                                                    @if($plan->has_discount)
                                                        <span class="text-xs font-normal line-through text-slate-400">₹{{ $plan->price * 1.5 }}</span>
                                                    @endif
                                                @else
                                                    Free
                                                @endif
                                            </div>
                                            <a href="{{ route('checkout', $plan->code) }}"
                                               class="px-4 py-2 text-sm font-bold text-blue-600 transition-all bg-white border border-blue-600 rounded-lg shadow-sm hover:text-white hover:bg-blue-600">
                                                Attempt Now
                                            </a>
                                        </div>
                                    </div>
                                @endforeach

                            @endif
                        @endforeach

                    @else
                        {{-- Empty State --}}
                        <div class="flex flex-col items-center justify-center py-12 text-center border-2 border-dashed col-span-full border-slate-200 rounded-xl">
                            <p class="text-slate-500">No active test series found in this category.</p>
                        </div>
                    @endif

                </template>
            @endforeach

        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('pricing') }}" class="px-8 py-3 font-bold transition bg-white border shadow-sm border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50">
                View All Test Series
            </a>
        </div>
    </div>
</section>

{{-- Helper Style for Animation --}}
<style>
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
