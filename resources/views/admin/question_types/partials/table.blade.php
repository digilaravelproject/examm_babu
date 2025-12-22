<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="w-24 px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Type Name</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Short Description</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                    {{-- Uncomment below if you add Action buttons like Edit/Delete --}}
                    {{-- <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th> --}}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($types as $type)
                    <tr class="transition-colors hover:bg-gray-50/80 group">
                        {{-- Code --}}
                        <td class="px-4 py-4">
                            <span class="px-1.5 py-0.5 font-mono text-[11px] font-medium bg-blue-50 text-[#0777be] rounded whitespace-nowrap">
                                {{ $type->code }}
                            </span>
                        </td>

                        {{-- Name --}}
                        <td class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $type->name }}
                            </div>
                        </td>

                        {{-- Description --}}
                        <td class="px-4 py-4">
                            <div class="text-xs text-gray-500 italic line-clamp-1">
                                {{ $type->short_description ?? 'No specific description.' }}
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4 text-center">
                            @if ($type->is_active)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-[#94c940]/10 text-[#94c940] whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-[#94c940]"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-600 rounded-full bg-orange-50 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-orange-500"></span> Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Actions (Optional structure if needed later) --}}
                        {{--
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="#" class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover-edit-btn group/btn">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                        --}}
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <p class="font-medium text-gray-500">No question types found matching your search.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($types->hasPages())
        <div class="px-4 py-3 bg-white border-t border-gray-200 pagination-wrapper">
            {{ $types->links() }}
        </div>
    @endif
</div>
