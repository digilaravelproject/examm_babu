<div class="mb-6 overflow-x-auto">
    <nav class="flex p-1 space-x-2 bg-white border border-gray-200 shadow-sm rounded-xl" aria-label="Tabs">
        <a href="{{ route('admin.settings.general') }}"
            class="{{ request()->routeIs('admin.settings.general') ? 'bg-[#0777be] text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}
                  px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                </path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            General
        </a>

        <a href="{{ route('admin.settings.email') }}"
            class="{{ request()->routeIs('admin.settings.email') ? 'bg-[#0777be] text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}
                  px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
            Email (SMTP)
        </a>

        <a href="{{ route('admin.settings.payment') }}"
            class="{{ request()->routeIs('admin.settings.payment') ? 'bg-[#0777be] text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}
                  px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            Payment (Razorpay)
        </a>

        <a href="{{ route('admin.settings.tax') }}"
            class="{{ request()->routeIs('admin.settings.tax') ? 'bg-[#0777be] text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}
                  px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                </path>
            </svg>
            Tax / GST
        </a>

        <a href="{{ route('admin.settings.billing') }}"
            class="{{ request()->routeIs('admin.settings.billing') ? 'bg-[#0777be] text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}
                  px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Billing & Invoice
        </a>

        <a href="{{ route('admin.settings.ai') }}"
            class="{{ request()->routeIs('admin.settings.ai') ? 'bg-[#0777be] text-white shadow' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}
                  px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            AI Settings
        </a>
    </nav>
</div>
