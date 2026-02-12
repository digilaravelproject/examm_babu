@php
    // Determine Route Prefix and Params dynamically
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);
    $params = (!$isAdmin && $currentRole) ? ['role' => $currentRole] : [];

    // FIX: Change 'sub_category' to 'id' to match the route definition: sub-categories/{id}/sections
    $updateParams = array_merge($params, ['id' => $subCategory->id]);
@endphp

<form id="section-mapping-form" action="{{ route($routePrefix . 'sub-categories.sections.update', $updateParams) }}" method="POST">
    @csrf

    <div class="mb-4">
        <p class="text-sm text-gray-500">
            Mapping sections for: <span class="font-bold text-[#0777be]">{{ $subCategory->name }}</span>
        </p>
    </div>

    <div class="p-2 space-y-3 overflow-y-auto border rounded-lg max-h-60 bg-gray-50">
        @forelse($allSections as $section)
            @php
                $isChecked = $subCategory->sections->contains($section->id);
            @endphp
            <div class="flex items-center justify-between p-3 bg-white border rounded-lg shadow-sm hover:border-[#0777be]/30 transition-colors">
                <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900">{{ $section->name }}</span>
                    <span class="text-xs text-gray-500">Code: {{ $section->code ?? 'N/A' }}</span>
                </div>

                {{-- Toggle Switch UI --}}
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="sections[]" value="{{ $section->id }}" class="sr-only peer" {{ $isChecked ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#0777be]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                </label>
            </div>
        @empty
            <div class="py-4 text-center text-gray-500">
                No active sections available to map.
            </div>
        @endforelse
    </div>

    <div class="mt-4 text-xs text-center text-gray-400">
        Toggle the switch to enable/disable sections for this sub-category.
    </div>
</form>
