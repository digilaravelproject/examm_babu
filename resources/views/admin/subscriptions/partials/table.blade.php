<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        {{-- Table Head: Standard Gray-50 --}}
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CODE</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PLAN</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">USER</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">STARTS</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ENDS</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PAYMENT</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">STATUS</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">ACTIONS</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($subscriptions as $sub)
                <tr class="hover:bg-gray-50 transition-colors">

                    {{-- 1. CODE (Bright Blue Badge - Fixed) --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-[#2196f3] text-white shadow-sm">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $sub->code ?? 'N/A' }}
                        </span>
                    </td>

                    {{-- 2. PLAN --}}
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 leading-tight max-w-[150px]">
                            {{ $sub->plan->name ?? 'N/A' }}
                        </div>
                    </td>

                    {{-- 3. USER (Email) --}}
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600">{{ $sub->user->email ?? 'N/A' }}</div>
                    </td>

                    {{-- 4. STARTS --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $sub->starts_at ? \Carbon\Carbon::parse($sub->starts_at)->format('M d, Y') : '-' }}
                    </td>

                    {{-- 5. ENDS --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $sub->ends_at ? \Carbon\Carbon::parse($sub->ends_at)->format('M d, Y') : '-' }}
                    </td>

                    {{-- 6. PAYMENT --}}
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $sub->payment_id ?? 'N/A' }}
                    </td>

                    {{-- 7. STATUS --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $status = strtolower($sub->status);
                            // Light colors for clean UI
                            $color = 'bg-gray-100 text-gray-700';
                            if ($status == 'active') {
                                $color = 'bg-green-100 text-green-700';
                            }
                            if ($status == 'expired') {
                                $color = 'bg-red-50 text-red-600';
                            }
                            if ($status == 'cancelled') {
                                $color = 'bg-gray-100 text-gray-600';
                            }
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase {{ $color }}">
                            {{ $status }}
                        </span>
                    </td>

                    {{-- 8. ACTIONS --}}
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button @click="open = !open" type="button"
                                class="bg-white rounded-md p-1.5 inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none ring-1 ring-gray-200 transition-all">
                                <span class="sr-only">Open options</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" style="display: none;"
                                class="origin-top-right absolute right-0 mt-2 w-36 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100">
                                <div class="py-1">
                                    <button
                                        @click="$dispatch('open-drawer', { type: 'edit', id: {{ $sub->id }} }); open = false"
                                        class="group flex items-center w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                                        <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Details
                                    </button>
                                </div>
                                <div class="py-1">
                                    <button @click="deleteSubscription({{ $sub->id }}); open = false"
                                        class="group flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="mr-3 h-4 w-4 text-red-400 group-hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>No subscriptions found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if (isset($subscriptions) && $subscriptions->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-white">
        {{ $subscriptions->appends(request()->query())->links() }}
    </div>
@endif
