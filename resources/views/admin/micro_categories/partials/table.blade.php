@php
    // Defensive coding: If controller fails to pass these during AJAX, calculate them here.
    if (!isset($routePrefix)) {
        $isAdmin = request()->routeIs('admin.*');
        $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    }
    
    if (!isset($routeParams)) {
        $currentRole = request()->route('role') ?? request()->segment(1);
        $routeParams = (!request()->routeIs('admin.*') && $currentRole) ? ['role' => $currentRole] : [];
    }
@endphp
<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Micro Category</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Parent Sub-Category</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-gray-500 uppercase">Created By</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($microCategories as $micro)
                    <tr class="transition-colors hover:bg-gray-50/80 group">
                        {{-- Code --}}
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 bg-blue-50 text-[#0777be] text-[10px] font-mono font-medium rounded whitespace-nowrap">
                                {{ $micro->code }}
                            </span>
                        </td>

                        {{-- Name & Image --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if ($micro->image_path)
                                    <img src="{{ asset($micro->image_path) }}" class="object-cover w-10 h-10 border border-gray-100 rounded-lg shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-[#7fd2ea]/20 flex items-center justify-center text-[#0777be] font-bold text-lg">
                                        {{ mb_substr($micro->name, 0, 1, 'UTF-8') }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $micro->name }}</span>
                            </div>
                        </td>

                        {{-- Parent Sub --}}
                        <td class="px-4 py-4 text-sm text-gray-600">
                            {{ $micro->subCategory->name ?? 'N/A' }}
                            @if (isset($micro->subCategory->category))
                                <span class="text-[10px] text-gray-400 block">in {{ $micro->subCategory->category->name }}</span>
                            @endif
                        </td>

                        {{-- Created By --}}
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $micro->creator->fullname ?? 'System' }}
                                </span>
                                <span class="text-[10px] text-gray-400">
                                    {{ $micro->creator ? $micro->creator->getRoleNames()->first() : 'Admin' }}
                                </span>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4 text-center">
                            @if ($micro->is_active)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-[#94c940]/10 text-[#94c940]">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-600 rounded-full bg-orange-50">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @php
                                    // PERMISSION CHECK
                                    $canEdit = auth()->user()->hasRole('admin') || $micro->created_by == auth()->id();

                                    // URL Construction with Dynamic Params
                                    $editUrl = route($routePrefix . 'micro-categories.edit', array_merge($routeParams ?? [], ['micro_category' => $micro->id]));
                                    $deleteUrl = route($routePrefix . 'micro-categories.destroy', array_merge($routeParams ?? [], ['micro_category' => $micro->id]));
                                @endphp

                                @if($canEdit)
                                    <a href="{{ $editUrl }}" class="flex items-center justify-center w-8 h-8 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover-edit-btn">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <button type="button" onclick="confirmDelete({{ $micro->id }}, '{{ addslashes($micro->name) }}')"
                                        class="flex items-center justify-center w-8 h-8 text-gray-500 transition-all bg-white border border-gray-200 rounded-lg shadow-sm hover-delete-btn">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>

                                    <form id="delete-form-{{ $micro->id }}" action="{{ $deleteUrl }}" method="POST" style="display: none;">
                                        @csrf @method('DELETE')
                                    </form>
                                @else
                                    <button disabled class="flex items-center justify-center w-8 h-8 border border-gray-200 rounded-lg bg-gray-50 btn-disabled" title="You cannot edit this item">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <p class="font-medium text-gray-500">No micro-categories found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 pagination-wrapper">
    {{ $microCategories->links() }}
</div>
