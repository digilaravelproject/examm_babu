@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('content')

@php
    // --- Dynamic Route Logic ---
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';

    // Prepare Parameters
    $routeParams = [];
    if (!$isAdmin) {
        $routeParams = ['role' => request()->route('role') ?? 'instructor'];
    }

    // Pre-generate URLs
    $urlIndex = route($routePrefix . 'exam-types.index', $routeParams);
    $urlStore = route($routePrefix . 'exam-types.store', $routeParams);
@endphp

    <div class="relative px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- 1. HEADER --}}
        <div class="flex flex-col items-center justify-between gap-4 mb-6 md:flex-row">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Exam Types</h1>
                <p class="text-sm text-gray-500">Manage your exam categories</p>
            </div>

            {{-- Add Button (Opens Drawer) --}}
            <button onclick="openDrawer()" style="background-color: var(--brand-blue);"
                class="flex items-center gap-2 px-5 py-2.5 font-bold text-white rounded-xl shadow hover:shadow-lg transition-all active:scale-95 text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Exam Type
            </button>
        </div>

        {{-- 2. FILTER SECTION --}}
        <div class="flex flex-col gap-4 p-4 mb-6 bg-white border border-gray-200 shadow-sm rounded-xl md:flex-row">

            {{-- Search Input --}}
            <div class="relative w-full md:w-1/3">
                <input type="text" id="searchInput" placeholder="Search by name or code..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)] bg-gray-50/50 outline-none transition-all">
                <svg class="absolute w-5 h-5 text-gray-400 left-3 top-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            {{-- Status Dropdown --}}
            <div class="w-full md:w-48">
                <select id="statusFilter"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)] bg-gray-50/50 outline-none cursor-pointer">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            {{-- Loader (Hidden by default) --}}
            <div id="filterLoader" class="hidden flex items-center text-[var(--brand-blue)]">
                <svg class="w-5 h-5 mr-3 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-sm font-medium">Filtering...</span>
            </div>
        </div>

        {{-- 3. TABLE CONTAINER (AJAX will update this) --}}
        <div id="tableContainer" class="mb-8 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            {{-- Pass route vars to partial --}}
            @include('admin.exam-types._table', ['routePrefix' => $routePrefix, 'routeParams' => $routeParams])
        </div>

    </div>

    {{-- ========================================== --}}
    {{-- SLIDE-OVER DRAWER (SIDEBAR MODAL) --}}
    {{-- ========================================== --}}

    {{-- Overlay Backdrop --}}
    <div id="drawerBackdrop" onclick="closeDrawer()"
        class="fixed inset-0 bg-gray-900/50 z-[99] hidden transition-opacity opacity-0"></div>

    {{-- Sidebar Panel --}}
    <div id="drawerPanel"
        class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-[100] transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">

        {{-- Drawer Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-800">New Exam Type</h2>
            <button onclick="closeDrawer()"
                class="p-2 text-gray-400 transition-colors rounded-full hover:text-gray-600 hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Drawer Form --}}
        {{-- FIX: Dynamic Store Route --}}
        <form action="{{ $urlStore }}" method="POST" class="p-6 space-y-6">
            @csrf

            {{-- 1. Exam Type Name --}}
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-700">Exam Type Name</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-2 transition-all border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- 2. Color Input --}}
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-700">Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="colorPicker" name="color" value="#ff0000"
                        class="w-12 h-10 p-0 border-0 rounded cursor-pointer"
                        onchange="document.getElementById('colorText').value = this.value">
                    <input type="text" id="colorText" value="#ff0000"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
                        onkeyup="document.getElementById('colorPicker').value = this.value">
                </div>
            </div>

            {{-- 3. Image URL Input --}}
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-700">Image URL</label>
                <input type="url" name="image_path"
                    class="w-full px-4 py-2 transition-all border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="https://example.com/image.png">
            </div>

            {{-- 4. Description --}}
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-700">Description</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2 transition-all border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            {{-- 5. Active Toggle --}}
            <div class="flex items-center justify-between pt-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Active</label>
                    <span class="text-xs text-gray-500">Active (Shown Everywhere). In-active (Hidden).</span>
                </div>

                {{-- CUSTOM CSS TO FORCE COLOR --}}
                <style>
                    /* Jab checkbox checked ho, to div ka background color change karein */
                    #activeCheckbox:checked+.toggle-bg {
                        background-color: #1a3c60 !important;
                    }
                </style>

                <label class="relative inline-flex items-center cursor-pointer">
                    {{-- Input par ID lagayi hai --}}
                    <input type="checkbox" id="activeCheckbox" name="is_active" value="1" class="sr-only peer"
                        checked>

                    {{-- Div par class 'toggle-bg' add ki hai targeting ke liye --}}
                    <div
                        class="toggle-bg w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#1a3c60]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                    </div>
                </label>
            </div>

            {{-- Footer Buttons --}}
            <div class="pt-6 mt-6 border-t border-gray-100">
                <button type="submit" style="background-color: var(--brand-blue);"
                    class="px-6 py-2.5 text-white font-bold rounded-lg shadow-lg hover:opacity-90 transition-all w-full md:w-auto">
                    Create
                </button>
            </div>
        </form>
    </div>

    {{-- COMBINED JAVASCRIPT (AJAX + DRAWER) --}}
    <script>
        // --- 1. DRAWER LOGIC ---
        const drawerPanel = document.getElementById('drawerPanel');
        const drawerBackdrop = document.getElementById('drawerBackdrop');

        function openDrawer() {
            drawerBackdrop.classList.remove('hidden');
            setTimeout(() => drawerBackdrop.classList.remove('opacity-0'), 10);
            drawerPanel.classList.remove('translate-x-full');
        }

        function closeDrawer() {
            drawerPanel.classList.add('translate-x-full');
            drawerBackdrop.classList.add('opacity-0');
            setTimeout(() => drawerBackdrop.classList.add('hidden'), 300);
        }

        // --- 2. AJAX FILTER LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {
            // Auto open drawer if validation errors exist
            @if ($errors->any())
                openDrawer();
            @endif

            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const tableContainer = document.getElementById('tableContainer');
            const loader = document.getElementById('filterLoader');

            let debounceTimer;

            // FIX: Dynamic Index URL for JS
            function fetchResults(url = "{{ $urlIndex }}") {
                // Show Loader
                if (loader) loader.classList.remove('hidden');

                // Build Params
                const params = new URLSearchParams(new URL(url).search);
                if (searchInput.value) params.set('search', searchInput.value);
                if (statusFilter.value) params.set('status', statusFilter.value);

                const finalUrl = `${url.split('?')[0]}?${params.toString()}`;

                fetch(finalUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        attachPaginationListeners(); // Re-attach click events
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        if (loader) loader.classList.add('hidden');
                    });
            }

            // Pagination Click Handler
            function attachPaginationListeners() {
                const links = document.querySelectorAll('.pagination-wrapper a');
                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        let url = this.getAttribute('href');
                        fetchResults(url);
                    });
                });
            }

            // Event Listeners
            searchInput.addEventListener('keyup', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchResults(), 300); // 300ms Delay
            });

            statusFilter.addEventListener('change', () => {
                fetchResults();
            });

            // Initialize Pagination
            attachPaginationListeners();
        });
    </script>
@endsection
