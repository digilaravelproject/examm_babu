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
    <table class="w-full text-left border-collapse">
        <thead class="border-b bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Sub-Category</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Created By</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Category</th>
                <th class="px-4 py-3 text-xs font-bold text-center text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-bold text-right text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($subCategories as $sub)
                <tr class="transition-colors hover:bg-gray-50/80">
                    <td class="px-4 py-4"><span
                            class="px-2 py-1 bg-blue-50 text-[#0777be] text-[10px] font-mono rounded">{{ $sub->code }}</span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            @if ($sub->image_path)
                                <img src="{{ asset($sub->image_path) }}" class="object-cover w-10 h-10 rounded-lg">
                            @else
                                <div
                                    class="w-10 h-10 rounded-lg bg-[#7fd2ea]/20 flex items-center justify-center text-[#0777be] font-bold text-lg">
                                    {{ mb_substr($sub->name, 0, 1, 'UTF-8') }}
                                </div>
                            @endif
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">{{ $sub->name }}</span>
                                <span class="text-[10px] text-gray-400">{{ $sub->micro_categories_count }}
                                    Micro-cats</span>
                            </div>
                        </div>
                    </td>
                    {{-- Created By --}}
                    <td class="px-4 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-700">
                                {{ $sub->creator->fullname ?? 'System' }}
                            </span>
                            <span class="text-[10px] text-gray-400">
                                {{ $sub->creator ? $sub->creator->getRoleNames()->first() : 'Admin' }}
                            </span>
                        </div>
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600">{{ $sub->category->name ?? 'N/A' }}</td>
                    <td class="px-4 py-4 text-center">
                        <span
                            class="px-2 py-1 text-[10px] font-bold rounded-full {{ $sub->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                            {{ $sub->is_active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            @php
                                // PERMISSION CHECK
                                $canEdit = auth()->user()->hasRole('admin') || $sub->created_by == auth()->id();

                                $editUrl = route(
                                    $routePrefix . 'sub-categories.edit',
                                    array_merge($routeParams ?? [], ['sub_category' => $sub->id]),
                                );
                                $deleteUrl = route(
                                    $routePrefix . 'sub-categories.destroy',
                                    array_merge($routeParams ?? [], ['sub_category' => $sub->id]),
                                );

                                // Safe Name for JS
                                $safeName = addslashes($sub->name);
                                $microCount = $sub->micro_categories_count ?? 0;
                            @endphp

                            @if ($canEdit)
                                {{-- Mapping Button --}}
                                <button type="button" data-id="{{ $sub->id }}"
                                    class="map-sections-btn p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg"
                                    title="Map Sections">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>

                                {{-- Edit Button --}}
                                <a href="{{ $editUrl }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            stroke-width="2" />
                                    </svg>
                                </a>

                                {{-- Delete Button --}}
                                <button type="button"
                                    onclick="confirmDelete({{ $sub->id }}, '{{ $safeName }}', {{ $microCount }})"
                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            stroke-width="2" />
                                    </svg>
                                </button>

                                <form id="delete-form-{{ $sub->id }}" action="{{ $deleteUrl }}" method="POST"
                                    style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            @else
                                <span class="pr-2 text-xs italic text-gray-400">Read Only</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-gray-400">No sub-categories found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 pagination-wrapper">{{ $subCategories->links() }}</div>
</div>
