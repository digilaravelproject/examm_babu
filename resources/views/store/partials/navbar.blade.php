<header x-data="{ mobileOpen: false, scrolled: false, megaMenu: null }" @scroll.window="scrolled = (window.pageYOffset > 20)"
    class="fixed top-0 z-50 w-full transition-all duration-300"
    :class="scrolled ? 'glass-nav shadow-sm py-2' : 'bg-transparent py-4'">

    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2 group">
                    <div class="flex items-center justify-center w-10 h-10 text-xl font-extrabold text-white transition-transform duration-300 shadow-lg rounded-xl group-hover:rotate-12"
                         style="background: linear-gradient(to bottom right, var(--brand-blue), var(--brand-sky));">
                        E
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-slate-800">Exam<span style="color: var(--brand-blue);">Babu</span></span>
                </a>

                <nav class="hidden gap-1 md:flex">
                    <div class="relative" @mouseenter="megaMenu = 'exams'" @mouseleave="megaMenu = null">
                        <button class="flex items-center gap-1 px-4 py-2 text-sm font-bold transition-all rounded-full text-slate-700 hover:bg-slate-100"
                                :style="megaMenu === 'exams' ? 'color: var(--brand-blue);' : ''">
                            Exams
                            <svg class="w-4 h-4 transition-transform duration-300"
                                :class="megaMenu === 'exams' ? 'rotate-180' : 'text-slate-400'"
                                :style="megaMenu === 'exams' ? 'color: var(--brand-blue);' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="megaMenu === 'exams'" x-data="{ activeCat: 'Police Exams' }"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-4"
                            class="absolute left-0 top-full mt-2 w-[950px] bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50 flex mega-menu-enter h-[600px]"
                            style="left: -150px;">

                            <div class="w-1/3 py-3 overflow-y-auto border-r bg-slate-50 border-slate-100">
                                @if(isset($examCategories))
                                    @foreach ($examCategories as $catName => $data)
                                        <button @mouseenter="activeCat = '{{ $catName }}'"
                                            class="flex items-center w-full gap-3 px-5 py-3 text-sm font-bold text-left transition-all duration-200 border-l-4"
                                            :class="activeCat === '{{ $catName }}' ? 'bg-white shadow-sm' : 'text-slate-600 border-transparent hover:bg-slate-100'"
                                            :style="activeCat === '{{ $catName }}' ? 'color: var(--brand-blue); border-color: var(--brand-blue);' : ''">
                                            <span class="text-base">{{ $data['icon'] }}</span>
                                            {{ $catName }}
                                            <svg x-show="activeCat === '{{ $catName }}'" class="w-4 h-4 ml-auto" style="color: var(--brand-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    @endforeach
                                @endif
                            </div>

                            <div class="w-2/3 p-6 overflow-y-auto bg-white">
                                @if(isset($examCategories))
                                    @foreach ($examCategories as $catName => $data)
                                        <div x-show="activeCat === '{{ $catName }}'" class="flex flex-col h-full">
                                            <div class="flex items-center justify-between pb-2 mb-6 border-b border-slate-100">
                                                <h3 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
                                                    {{ $data['icon'] }} Popular {{ $catName }}
                                                </h3>
                                                <a href="#" class="flex items-center gap-1 text-xs font-bold hover:underline" style="color: var(--brand-blue);">
                                                    View All
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </a>
                                            </div>

                                            @if (isset($data['grouped']) && $data['grouped'])
                                                <div class="space-y-6">
                                                    @foreach ($data['groups'] as $groupName => $exams)
                                                        <div>
                                                            <h4 class="pb-1 mb-2 text-xs font-bold tracking-wider uppercase border-b text-slate-400 border-slate-100">{{ $groupName }}</h4>
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach ($exams as $exam)
                                                                    <a href="#" class="flex items-center gap-2 p-2 transition-all rounded hover:bg-blue-50 group">
                                                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300 transition-colors" style="group-hover:background-color: var(--brand-blue);"></div>
                                                                        <span class="text-sm font-medium truncate text-slate-700" style="group-hover:color: var(--brand-blue);" title="{{ $exam }}">{{ $exam }}</span>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="grid content-start grid-cols-2 gap-3">
                                                    @foreach ($data['exams'] as $exam)
                                                        <a href="#" class="flex items-center gap-3 p-3 transition-all border border-transparent rounded-xl hover:bg-blue-50 hover:border-blue-100 group">
                                                            <div class="flex items-center justify-center w-8 h-8 text-xs font-bold transition-colors rounded-lg shrink-0 bg-slate-100 text-slate-500 group-hover:text-white"
                                                                 style="group-hover:background-color: var(--brand-blue);">
                                                                {{ substr($exam, 0, 1) }}
                                                            </div>
                                                            <span class="text-sm font-semibold text-slate-700" style="group-hover:color: var(--brand-blue);">{{ $exam }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <a href="#" class="px-4 py-2 text-sm font-bold transition-colors rounded-full text-slate-700 hover:bg-slate-100" style="hover:color: var(--brand-blue);">Test Series</a>
                    <a href="#" class="px-4 py-2 text-sm font-bold transition-colors rounded-full text-slate-700 hover:bg-slate-100" style="hover:color: var(--brand-blue);">Super Coaching</a>
                    <a href="#" class="px-4 py-2 text-sm font-bold transition-colors rounded-full text-slate-700 hover:bg-slate-100" style="hover:color: var(--brand-blue);">Pass Pro</a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="relative hidden lg:flex group">
                    <input type="text" placeholder="Search exams..."
                        class="pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent rounded-full text-sm font-medium text-slate-700 focus:ring-2 focus:bg-white w-64 transition-all duration-300 shadow-inner"
                        style="focus:border-color: var(--brand-sky); focus:ring-color: var(--brand-blue);">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5 group-focus-within:text-blue-500 transition-colors"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                @auth
                    <a href="{{ route('home') }}" class="font-bold text-slate-700 hover:text-blue-600">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden px-4 text-sm font-bold sm:block text-slate-700 hover:text-blue-600">Log in</a>
                    <a href="{{ route('register') }}"
                        class="text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all transform"
                        style="background: linear-gradient(to right, var(--brand-blue), #055a91); box-shadow: 0 10px 15px -3px rgba(7, 119, 190, 0.3);">
                        Get Started
                    </a>
                @endauth

                <button @click="mobileOpen = !mobileOpen" class="p-2 md:hidden text-slate-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" x-transition class="fixed inset-0 z-50 overflow-y-auto bg-white md:hidden">
        <div class="flex items-center justify-between p-4 border-b border-slate-100">
            <span class="text-xl font-bold">Menu</span>
            <button @click="mobileOpen = false" class="p-2"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        <div class="p-4 space-y-4">
            <a href="#" class="block text-lg font-medium text-slate-800">Exams</a>
            <a href="#" class="block text-lg font-medium text-slate-800">Test Series</a>
            <a href="#" class="block text-lg font-medium text-slate-800">Super Coaching</a>
            <div class="h-px my-4 bg-slate-100"></div>
            <a href="{{ route('login') }}" class="block w-full py-3 font-bold text-center border border-slate-200 rounded-xl">Log in</a>
            <a href="{{ route('register') }}" class="block w-full py-3 font-bold text-center text-white rounded-xl" style="background-color: var(--brand-blue);">Sign up Free</a>
        </div>
    </div>
</header>
