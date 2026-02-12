@extends('layouts.admin')

@section('header', 'User Specific Commissions')

@section('content')
<div class="mx-auto space-y-6 max-w-7xl">

    {{-- Top Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Influencer/User Commissions</h1>
            <p class="text-sm text-gray-500">Override global settings for specific users.</p>
        </div>

        <div class="px-4 py-2 text-xs font-semibold text-blue-800 rounded-lg bg-blue-50">
            Global Default: New {{ $globalSettings->commission_percentage }}% | Recurring {{ $globalSettings->recurring_commission_percentage }}%
        </div>
    </div>

    {{-- Search Box --}}
    <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            {{-- ID added for JS --}}
            <input type="text" id="searchInput"
                   placeholder="Type to search users..."
                   class="w-full pl-10 px-4 py-2 border rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">

            {{-- Loading Spinner --}}
            <div id="searchSpinner" class="absolute inset-y-0 right-0 flex items-center hidden pr-3">
                <svg class="w-5 h-5 text-[#0777be] animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl relative min-h-[300px]">

        {{-- Table Content --}}
        <table class="w-full text-sm text-left">
            <thead class="font-bold text-gray-600 uppercase border-b bg-gray-50">
                <tr>
                    <th class="px-6 py-4">User Details</th>
                    <th class="px-6 py-4 text-center">New User %</th>
                    <!--<th class="px-6 py-4 text-center">Recurring %</th>-->
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>
            {{-- ID added for JS target --}}
            <tbody id="usersTableBody" class="divide-y divide-gray-100">
                @include('admin.referral.partials.table_rows', ['users' => $users])
            </tbody>
        </table>

        {{-- Pagination Container --}}
        <div id="paginationContainer" class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>

        {{-- Overlay Spinner (for Pagination clicks) --}}
        <div id="overlayLoader" class="absolute inset-0 z-10 flex items-center justify-center hidden bg-white/50 backdrop-blur-sm">
            <div class="flex flex-col items-center">
                <svg class="w-8 h-8 text-[#0777be] animate-spin mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-bold text-[#0777be]">Loading...</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('usersTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const searchSpinner = document.getElementById('searchSpinner');
        const overlayLoader = document.getElementById('overlayLoader');

        let debounceTimer;

        // --- 1. SEARCH FUNCTION ---
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);

            // Show input spinner
            searchSpinner.classList.remove('hidden');

            debounceTimer = setTimeout(() => {
                const query = this.value;
                fetchUsers(query);
            }, 500); // 500ms delay wait for user to stop typing
        });

        // --- 2. AJAX FETCH FUNCTION ---
        function fetchUsers(query, url = null) {
            // URL construct (Search + URL from pagination)
            const fetchUrl = url ? url : `{{ route('admin.referral.users') }}?search=${query}`;

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update HTML
                tableBody.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;

                // Hide Loaders
                searchSpinner.classList.add('hidden');
                overlayLoader.classList.add('hidden');

                // Re-attach pagination listeners because HTML was replaced
                attachPaginationListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                searchSpinner.classList.add('hidden');
                overlayLoader.classList.add('hidden');
            });
        }

        // --- 3. PAGINATION HANDLING ---
        function attachPaginationListeners() {
            // Select all pagination links
            const links = paginationContainer.querySelectorAll('a');

            links.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Show full overlay loader
                    overlayLoader.classList.remove('hidden');

                    const url = this.href;
                    // Current search query maintain rakhne ke liye
                    const query = searchInput.value;

                    // Naya URL banao jisme search query bhi ho agar URL me nahi h
                    const finalUrl = new URL(url);
                    if(query) {
                        finalUrl.searchParams.set('search', query);
                    }

                    fetchUsers(query, finalUrl.toString());
                });
            });
        }

        // Initial Attach
        attachPaginationListeners();
    });
</script>
@endsection
