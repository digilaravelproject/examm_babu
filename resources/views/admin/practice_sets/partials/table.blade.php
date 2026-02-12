<div class="overflow-x-auto min-h-[400px]"> {{-- Min-height added so dropdown isn't cut off --}}
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 font-medium">Code</th>
                <th class="px-6 py-3 font-medium">Title</th>
                <th class="px-6 py-3 font-medium">Sub Category</th>
                <th class="px-6 py-3 font-medium">Skill</th>
                <th class="px-6 py-3 font-medium">Status</th>
                <th class="px-6 py-3 font-medium text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($practiceSets as $set)
                <tr class="bg-white hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-mono text-[#0777be] font-semibold">{{ $set->code }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $set->title }}</td>
                    <td class="px-6 py-4">{{ $set->subCategory->name ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $set->skill->name ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if ($set->is_active)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                        @endif
                    </td>

                    {{-- ACTIONS DROPDOWN COLUMN --}}
                    <td class="px-6 py-4 text-right">
                        <div x-data="{ open: false }" class="relative inline-block text-left">

                            {{-- Trigger Button --}}
                            <button @click="open = !open" @click.outside="open = false" type="button"
                                class="inline-flex justify-center items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0777be]"
                                :class="{ 'border-[#0777be] ring-1 ring-[#0777be]': open }">
                                Actions
                                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            {{-- Dropdown Menu --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                                style="display: none;">

                                <div class="py-1" role="menu" aria-orientation="vertical">

                                    {{-- Settings --}}
                                    <a href="{{ route('admin.practice-sets.settings', $set->id) }}"
                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                        <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Settings
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.practice-sets.edit', $set->id) }}"
                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                        <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.practice-sets.destroy', $set->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this practice set?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="group flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-900">
                                            <svg class="mr-3 h-4 w-4 text-red-500 group-hover:text-red-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            <p class="text-base font-medium">No Practice Sets Found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-6 py-4 border-t border-gray-200">
    {{ $practiceSets->links() }}
</div>
