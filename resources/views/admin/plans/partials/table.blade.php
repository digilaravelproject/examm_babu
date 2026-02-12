<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">Price/Month</th>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($plans['data'] as $plan)
                    <tr class="transition-colors hover:bg-gray-50 group">

                        {{-- Code --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                #{{ $plan['code'] ?? 'N/A' }}
                            </span>
                        </td>

                        {{-- Name --}}
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            {{ $plan['name'] }}
                        </td>

                        {{-- Duration --}}
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $plan['duration'] }} Months
                            </div>
                        </td>

                        {{-- Price --}}
                        <td class="px-6 py-4 text-sm font-bold text-emerald-600">
                            {{ $plan['price'] }}
                        </td>

                        {{-- Category --}}
                        <td class="px-6 py-4 text-sm text-gray-500">
                           <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                               {{ $plan['category'] ?? 'General' }}
                           </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $isActive = $plan['is_active'] ?? ($plan['status'] ?? false);
                            @endphp

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $isActive ? 'bg-green-600' : 'bg-red-600' }}"></span>
                                {{ $isActive ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        {{-- Actions (Updated to Exam Babu Style) --}}
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-3">
                                {{-- Edit Button --}}
                                <a href="{{ route('admin.plans.edit', $plan['id']) }}"
                                   class="p-2 text-blue-500 transition-all duration-200 rounded-lg hover:text-blue-700 hover:bg-blue-50"
                                   title="Edit Plan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>

                                {{-- Delete Button --}}
                                <button type="button"
                                    onclick="deletePlan({{ $plan['id'] }}, '{{ $plan['name'] }}')"
                                    class="p-2 text-red-500 transition-all duration-200 rounded-lg hover:text-red-700 hover:bg-red-50"
                                    title="Delete Plan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <span class="text-base font-medium">No plans found</span>
                                <span class="mt-1 text-sm">Try adjusting your filters or create a new plan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Section --}}
    @if (isset($paginator) && $paginator->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $paginator->appends(request()->query())->links() }}
        </div>
    @endif
</div>
