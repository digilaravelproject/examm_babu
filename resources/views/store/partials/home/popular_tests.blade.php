<section class="py-20 bg-white" x-data="{ currentTab: {{ $categories->first()->id ?? 'null' }} }">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div class="mb-12 text-center">
            <h2 class="mb-4 text-3xl font-extrabold lg:text-4xl text-slate-900">
                Popular Mock Tests
            </h2>
            <p class="text-lg text-slate-500">
                Attempt free mock tests curated by experts.
            </p>
        </div>

        {{-- CATEGORY TABS --}}
        <div class="flex flex-wrap justify-center gap-2 mb-12">
            @foreach ($categories as $category)
                <button @click="currentTab = {{ $category->id }}"
                    class="px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300"
                    :class="currentTab === {{ $category->id }} ?
                        'text-white shadow-lg shadow-blue-500/30 scale-105' :
                        'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    :style="currentTab === {{ $category->id }} ?
                        'background-color: var(--brand-blue);' :
                        ''">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        {{-- CONTENT GRID --}}
        <div class="min-h-[260px]">
            @foreach ($categories as $category)
                {{-- Tab Content --}}
                <div x-show="currentTab === {{ $category->id }}" x-cloak
                    class="grid w-full grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">

                    @if ($category->subCategories->flatMap->plans->count() > 0)
                        @foreach ($category->subCategories as $subCategory)
                            @if ($subCategory->plans->isNotEmpty())
                                @foreach ($subCategory->plans as $plan)
                                    @php
                                        // Price Calculation Logic
                                        $originalPrice = (float) $plan->price;
                                        $discountPercent = (int) $plan->discount_percentage;
                                        $hasDiscount = $plan->has_discount && $discountPercent > 0;

                                        $sellingPrice = $originalPrice;
                                        $savings = 0;

                                        if ($hasDiscount) {
                                            $savings = ($originalPrice * $discountPercent) / 100;
                                            $sellingPrice = $originalPrice - $savings;
                                        }

                                        $sellingPrice = round($sellingPrice);
                                        $savings = round($savings);
                                    @endphp

                                    {{-- PLAN CARD --}}
                                    <div
                                        class="relative flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1 animate-fade-in-up">

                                        {{-- ========================================================= --}}
                                        {{-- FIXED LINK: USING IDs INSTEAD OF SLUGS --}}
                                        {{-- ========================================================= --}}
                                        <a href="{{ route('exam_details.microcategory', ['subCategory' => $subCategory->id, 'microCategory' => $plan->id]) }}"
                                            class="absolute inset-0 z-10" title="View Details">
                                        </a>


                                        {{-- Card Body --}}
                                        <div class="relative flex-1 p-6 pointer-events-none">

                                            {{-- ICON --}}
                                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20">
                                                <svg class="w-16 h-16 text-blue-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5z" />
                                                </svg>
                                            </div>

                                            {{-- BADGES --}}
                                            <div class="flex flex-wrap items-center gap-2 mb-3 relative z-20">
                                                <span
                                                    class="px-2 py-1 text-xs font-bold text-blue-700 uppercase rounded-md bg-blue-50">
                                                    {{ $subCategory->name }}
                                                </span>

                                                @if ($hasDiscount)
                                                    <span
                                                        class="px-2 py-1 text-xs font-bold text-white uppercase bg-green-600 rounded-md animate-pulse">
                                                        FLAT {{ $discountPercent }}% OFF
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- TITLE --}}
                                            <h3
                                                class="mb-2 text-xl font-bold text-slate-800 group-hover:text-blue-600 line-clamp-2">
                                                {{ $plan->name }}
                                            </h3>

                                            {{-- DESCRIPTION --}}
                                            <p class="mb-4 text-sm text-slate-500 line-clamp-1">
                                                {{ $plan->description ?? 'Comprehensive Test Series' }}
                                            </p>

                                            {{-- STATS --}}
                                            <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                                                <span>⏱ {{ $plan->duration ?? 30 }} Days</span>
                                                <span>👥 {{ rand(100, 2000) }}+ Users</span>
                                            </div>
                                        </div>

                                        {{-- FOOTER --}}
                                        <div
                                            class="flex items-center justify-between p-4 border-t bg-slate-50/50 relative z-20">

                                            {{-- Price --}}
                                            <div class="pointer-events-none">
                                                @if ($originalPrice > 0)
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xl font-extrabold text-slate-900">
                                                            ₹{{ $sellingPrice }}
                                                        </span>
                                                        @if ($hasDiscount)
                                                            <span class="text-sm line-through text-slate-400">
                                                                ₹{{ $originalPrice }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if ($hasDiscount)
                                                        <span class="text-[10px] font-bold text-green-600">
                                                            SAVE ₹{{ $savings }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-xl font-bold text-green-600">Free</span>
                                                @endif
                                            </div>

                                            {{-- Attempt Button --}}
                                            @if (auth()->check() && auth()->user()->hasRole('admin'))
                                                <button disabled
                                                    class="px-5 py-2.5 text-sm font-bold text-gray-400 bg-gray-100 border rounded-lg cursor-not-allowed">
                                                    Admin View
                                                </button>
                                            @else
                                                <a href="{{ route('checkout', $plan->code) }}"
                                                    class="relative z-30 px-5 py-2.5 text-sm font-bold text-white rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                                    Attempt Now
                                                </a>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center col-span-full text-slate-500 py-12">
                            No active test series found in this category.
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        {{-- VIEW ALL --}}
        <div class="mt-12 text-center">
            <a href="{{ route('pricing') }}"
                class="px-8 py-3 font-bold border shadow-sm rounded-xl hover:bg-slate-50 transition-colors">
                View All Test Series
            </a>
        </div>
    </div>
</section>

<style>
    [x-cloak] {
        display: none !important;
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
