<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Duration</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Price/Month</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($plans['data'] as $plan)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 bg-blue-500 text-white text-[10px] font-bold rounded-md flex items-center w-fit">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    {{ $plan['code'] }}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                {{ $plan['name'] }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
                {{ $plan['duration'] }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 font-bold">
                {{ $plan['price'] }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                {{ $plan['category'] }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                @if($plan['status'])
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Active</span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">In-active</span>
                @endif
            </td>
            <td class="px-6 py-4 text-right text-sm">
                <div x-data="{ open: false }" class="relative inline-block text-left">
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 flex items-center border rounded px-2 py-1">
                        Actions <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    {{-- Action Dropdown here --}}
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-6 py-10 text-center text-gray-500">No plans found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Pagination Links --}}
<div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
    {{-- Pagination logic depends on your $plans['meta'] or standard Laravel paginator --}}
</div>