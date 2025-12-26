<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Duration
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Price/Month
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($plans['data'] as $plan)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- Code --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 py-1 bg-blue-500 text-white text-[10px] font-bold rounded-md flex items-center w-fit">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                    </path>
                                </svg>
                                {{ $plan['code'] ?? 'N/A' }}
                            </span>
                        </td>

                        {{-- Name --}}
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                            {{ $plan['name'] }}
                        </td>

                        {{-- Duration --}}
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $plan['duration'] }} Months
                        </td>

                        {{-- Price --}}
                        <td class="px-6 py-4 text-sm text-gray-900 font-bold">
                            {{ $plan['price'] }}
                        </td>

                        {{-- Category --}}
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $plan['category'] ?? 'N/A' }}
                        </td>

                        {{-- Status (FIXED HERE) --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{-- Check both 'is_active' and 'status' keys to prevent error --}}
                            @php
                                $isActive = $plan['is_active'] ?? ($plan['status'] ?? false);
                            @endphp

                            @if ($isActive)
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Active</span>
                            @else
                                <span
                                    class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">In-active</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right text-sm">
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <button @click="open = !open"
                                    class="inline-flex items-center justify-center w-full px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#10b981]">
                                    Actions
                                    <svg class="w-4 h-4 ml-1 -mr-1" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" style="display: none;"
                                    class="absolute right-0 z-50 w-36 mt-2 origin-top-right bg-white border border-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <div class="py-1">
                                        <a href="{{ route('admin.plans.edit', $plan['id']) }}"
                                            class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                            <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Edit
                                        </a>
                                        <button type="button"
                                            onclick="deletePlan({{ $plan['id'] }}, '{{ $plan['name'] }}')"
                                            class="group flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <svg class="mr-3 h-4 w-4 text-red-400 group-hover:text-red-500"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            No plans found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Section --}}
    @if (isset($paginator) && $paginator->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $paginator->appends(request()->query())->links() }}
        </div>
    @endif
</div>
