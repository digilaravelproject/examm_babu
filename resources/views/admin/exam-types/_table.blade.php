@php
    // FIX: Fallback logic if variables are not passed from parent
    if (!isset($routePrefix)) {
        $isAdmin = request()->routeIs('admin.*');
        $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    }

    if (!isset($routeParams)) {
        $routeParams = [];
        if ($routePrefix === 'panel.') {
            $routeParams = ['role' => request()->route('role') ?? 'instructor'];
        }
    }
@endphp

<div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200 bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-4 text-xs font-bold tracking-wider text-center text-gray-500 uppercase">Status
                    </th>
                    <th class="px-6 py-4 text-xs font-bold tracking-wider text-right text-gray-500 uppercase">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($examTypes as $type)
                    <tr class="transition-colors hover:bg-gray-50/80 group">
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 font-mono text-xs font-bold text-gray-600 border border-gray-200 rounded select-all bg-gray-50">
                                {{ $type->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="text-sm font-bold text-gray-900 group-hover:text-[var(--brand-blue)] transition-colors">
                                {{ $type->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($type->is_active)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- FIX: Dynamic Edit Route --}}
                                <a href="{{ route($routePrefix . 'exam-types.edit', array_merge($routeParams, ['exam_type' => $type->id])) }}"
                                    class="p-2 text-gray-400 hover:text-[var(--brand-blue)] hover:bg-[var(--brand-blue)]/10 rounded-lg transition-all"
                                    title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                </a>

                                {{-- FIX: Dynamic Delete Route --}}
                                <form action="{{ route($routePrefix . 'exam-types.destroy', array_merge($routeParams, ['exam_type' => $type->id])) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this type?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="p-2 text-gray-400 transition-all rounded-lg hover:text-red-600 hover:bg-red-50"
                                        title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 mb-2 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                <p>No exam types found matching your filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Wrapper Class --}}
    @if ($examTypes->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 pagination-wrapper">
            {{-- FIX: Append Params to Pagination --}}
            {{ $examTypes->appends($routeParams)->links() }}
        </div>
    @endif
</div>
