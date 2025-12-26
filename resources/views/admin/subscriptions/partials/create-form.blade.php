<div class="flex flex-col h-full bg-white">

    {{-- Form Content --}}
    <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="flex-1 overflow-y-auto p-6 space-y-6">
        @csrf

        {{-- 1. User Field --}}
        <div x-data="{
            open: false,
            search: '',
            selectedName: 'Choose User',
            selectedId: ''
        }" class="relative">
            <label class="block text-sm font-bold text-gray-700 mb-1">
                User <span class="text-red-500">*</span>
            </label>

            <input type="hidden" name="user_id" :value="selectedId" required>

            <button type="button" @click="open = !open; $nextTick(() => $refs.searchInput.focus())"
                class="w-full bg-white border border-gray-300 text-gray-500 text-sm rounded-lg px-4 py-2.5 text-left focus:ring-2 focus:ring-blue-500 focus:border-blue-500 flex justify-between items-center transition-colors">
                <span x-text="selectedName"
                    :class="{ 'text-gray-900': selectedId !== '', 'text-gray-500': selectedId === '' }"></span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" style="display: none;"
                class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-100">

                <div class="sticky top-0 z-10 bg-white px-2 py-2 border-b border-gray-100">
                    <input x-ref="searchInput" x-model="search" type="text"
                        class="w-full border-gray-200 bg-gray-50 rounded-md text-sm p-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Search user...">
                </div>

                <ul class="pt-1">
                    @foreach ($users as $user)
                        <li x-show="'{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}'.includes(search.toLowerCase())"
                            @click="selectedId = '{{ $user->id }}'; selectedName = '{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})'; open = false"
                            class="cursor-pointer select-none relative py-2.5 pl-3 pr-9 hover:bg-blue-50 hover:text-blue-700 text-gray-700 transition-colors border-b border-gray-50 last:border-0">
                            <div class="flex flex-col">
                                <span class="font-medium block truncate text-sm">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </span>
                                <span class="text-xs text-gray-400 truncate">
                                    {{ $user->email }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                    <li x-show="$el.parentNode.querySelectorAll('li[x-show]:not([style*=\'display: none\'])').length === 0"
                        class="py-3 pl-3 text-gray-400 text-sm text-center italic">
                        No users found.
                    </li>
                </ul>
            </div>
        </div>

        {{-- 2. Plan Field --}}
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">
                Plan <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select name="plan_id" required
                    class="appearance-none w-full bg-white border border-gray-300 text-gray-500 text-sm rounded-lg px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500 cursor-pointer hover:border-gray-400 transition-colors">
                    <option value="" disabled selected>Choose Plan</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->duration }} Months)
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- 3. Status Field --}}
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">
                Status <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select name="status" required
                    class="appearance-none w-full bg-white border border-gray-300 text-gray-500 text-sm rounded-lg px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500 cursor-pointer hover:border-gray-400 transition-colors">
                    <option value="" disabled selected>Choose Status</option>
                    <option value="active">Active</option>
                    <option value="created">Created</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="expired">Expired</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Hidden Start Date --}}
        <input type="hidden" name="starts_at" value="{{ now() }}">

        {{-- Submit Button (Changed Color to Standard Blue) --}}
        <div class="pt-6 mt-auto">
            <button type="submit"
                class="w-full bg-blue-600 text-white font-bold rounded-lg text-sm px-5 py-3 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                Save
            </button>
        </div>
    </form>
</div>
