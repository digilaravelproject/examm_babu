@php
    // --- AlpineJS Data Prep ---
    $groupsData = $userGroups->map(function($group) {
        return ['id' => $group->id, 'name' => $group->name];
    })->values();

    $selectedIds = [];
    if(old('user_groups')) {
        $selectedIds = array_map('intval', old('user_groups'));
    } elseif(isset($selectedGroups) && is_array($selectedGroups)) {
        $selectedIds = array_map('intval', $selectedGroups);
    }

    $rolesData = collect($roles)->map(function($role) {
        return ['value' => $role, 'label' => ucfirst($role)];
    })->values();

    $currentRole = old('role', $userRole ?? '');
    $isVerified = old('verify_email') ? true : (isset($user) && $user->email_verified_at != null);
@endphp

<div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

    {{-- LEFT COLUMN: Basic User Info --}}
    <div class="space-y-6 lg:col-span-8">

        <div class="p-6 border border-gray-100 rounded-xl bg-gray-50/50">
            <h3 class="flex items-center mb-4 text-lg font-bold leading-6 text-gray-800">
                <svg class="w-5 h-5 mr-2 text-[#0777be]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Personal Information
            </h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- First Name --}}
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" id="first_name"
                        value="{{ old('first_name', $user->first_name ?? '') }}"
                        class="block w-full py-2.5 mt-1 border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] sm:text-sm @error('first_name') border-red-500 @enderror"
                        required placeholder="John">
                    @error('first_name') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Last Name --}}
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name"
                        value="{{ old('last_name', $user->last_name ?? '') }}"
                        class="block w-full py-2.5 mt-1 border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] sm:text-sm @error('last_name') border-red-500 @enderror"
                        placeholder="Doe">
                    @error('last_name') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label for="user_name" class="block text-sm font-medium text-gray-700">Username <span class="text-red-500">*</span></label>
                    <div class="flex mt-1 rounded-lg shadow-sm">
                        <span class="inline-flex items-center px-3 text-gray-500 border border-r-0 border-gray-300 rounded-l-lg bg-gray-50 sm:text-sm">@</span>
                        <input type="text" name="user_name" id="user_name"
                            value="{{ old('user_name', $user->user_name ?? '') }}"
                            class="flex-1 block w-full min-w-0 py-2.5 border-gray-300 rounded-none rounded-r-lg focus:border-[#0777be] focus:ring-[#0777be] sm:text-sm @error('user_name') border-red-500 @enderror"
                            required placeholder="johndoe">
                    </div>
                    @error('user_name') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Mobile Number --}}
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                    <input type="text" name="mobile" id="mobile"
                        value="{{ old('mobile', $user->mobile ?? '') }}"
                        class="block w-full py-2.5 mt-1 border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] sm:text-sm @error('mobile') border-red-500 @enderror"
                        placeholder="9876543210">
                    @error('mobile') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Email Address --}}
                <div class="md:col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email"
                        value="{{ old('email', $user->email ?? '') }}"
                        class="block w-full py-2.5 mt-1 border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] sm:text-sm @error('email') border-red-500 @enderror"
                        required placeholder="john@example.com">
                    @error('email') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- User Groups Assignment (Multi-Select) --}}
        <div class="p-6 border border-gray-100 rounded-xl bg-gray-50/50"
             x-data="groupSelector({
                 allGroups: {{ json_encode($groupsData) }},
                 selectedIds: {{ json_encode($selectedIds) }}
             })"
             @click.outside="closeDropdown()">

            <h3 class="flex items-center mb-4 text-lg font-bold leading-6 text-gray-800">
                <svg class="w-5 h-5 mr-2 text-[#f062a4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                User Groups Assignment
            </h3>

            <label class="block mb-2 text-sm font-medium text-gray-700">Select Groups</label>

            <div class="relative">
                {{-- Input Area --}}
                <div class="bg-white border rounded-lg p-2 flex flex-wrap gap-2 min-h-[45px] focus-within:ring-1 focus-within:ring-[#0777be] focus-within:border-[#0777be] cursor-text shadow-sm transition-all"
                     :class="{'border-red-500': {{ $errors->has('user_groups') ? 'true' : 'false' }}, 'border-gray-300': !{{ $errors->has('user_groups') ? 'true' : 'false' }}}"
                     @click="openDropdown()">

                    <template x-for="group in selectedGroups" :key="group.id">
                        <span class="inline-flex items-center py-1 pl-2.5 pr-1 text-sm font-medium text-[#0777be] bg-blue-50 border border-blue-100 rounded-full">
                            <span x-text="group.name"></span>
                            <button type="button" @click.stop="removeGroup(group.id)" class="inline-flex items-center justify-center flex-shrink-0 w-4 h-4 ml-1 text-blue-400 transition-colors rounded-full hover:bg-blue-200 hover:text-blue-700 focus:outline-none">
                                <svg class="w-2 h-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                            </button>
                        </span>
                    </template>

                    <input type="text" x-ref="searchInput" x-model="search"
                           placeholder="Type to search..."
                           class="flex-1 min-w-[150px] bg-transparent border-none outline-none focus:ring-0 p-1 text-sm text-gray-700 placeholder-gray-400"
                           @keydown.backspace="if(search === '' && selectedIds.length > 0) removeGroup(selectedIds[selectedIds.length - 1])"
                           @focus="openDropdown()"
                           @keydown.escape="closeDropdown()"
                           @keydown.arrow-down.prevent="highlightNext()"
                           @keydown.arrow-up.prevent="highlightPrev()"
                           @keydown.enter.prevent="selectHighlighted()">
                </div>

                {{-- Dropdown List --}}
                <div x-show="isOpen && filteredGroups.length > 0"
                     class="absolute z-20 w-full py-1 mt-1 overflow-auto bg-white rounded-lg shadow-xl max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar"
                     style="display: none;" x-transition>

                    <template x-for="(group, index) in filteredGroups" :key="group.id">
                        <div @click="addGroup(group)"
                             @mouseenter="activeIndex = index"
                             :class="{ 'bg-[#0777be] text-white': activeIndex === index, 'text-gray-900': activeIndex !== index }"
                             class="relative py-2.5 pl-3 pr-9 cursor-pointer select-none transition-colors">
                            <span x-text="group.name" class="block font-normal truncate"></span>
                            <span x-show="selectedIds.includes(group.id)" class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#0777be]" :class="{ 'text-white': activeIndex === index }">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Hidden Inputs (Fix for submitting array) --}}
            <div class="hidden">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="user_groups[]" :value="id">
                </template>
            </div>
            {{-- <div class="mt-2 text-xs text-gray-500">Selected IDs: <span x-text="selectedIds"></span></div> --}}

            @error('user_groups') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- RIGHT COLUMN: Security & Settings --}}
    <div class="space-y-6 lg:col-span-4">
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h3 class="mb-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Security</h3>

            {{-- Custom Role Dropdown --}}
            <div class="mb-6"
                 x-data="roleSelector({
                     selected: '{{ $currentRole }}',
                     roles: {{ json_encode($rolesData) }}
                 })"
                 @click.outside="open = false">

                <label class="block mb-1 text-sm font-medium text-gray-700">User Role <span class="text-red-500">*</span></label>
                <div class="relative">
                    <button type="button" @click="open = !open"
                            class="relative w-full py-2.5 pl-3 pr-10 text-left bg-white border rounded-lg shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-[#0777be] focus:border-[#0777be] sm:text-sm"
                            :class="{'border-red-500': {{ $errors->has('role') ? 'true' : 'false' }}, 'border-gray-300': !{{ $errors->has('role') ? 'true' : 'false' }}}">
                        <span class="block truncate" x-text="displayValue"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </span>
                    </button>

                    <div x-show="open"
                         class="absolute z-10 w-full py-1 mt-1 overflow-auto bg-white rounded-lg shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar"
                         style="display: none;" x-transition>
                        <template x-for="role in roles" :key="role.value">
                            <div @click="selectRole(role.value)"
                                 class="relative py-2 pl-3 text-gray-900 transition-colors cursor-pointer select-none pr-9 hover:bg-[#0777be] hover:text-white">
                                <span :class="selected === role.value ? 'font-semibold' : 'font-normal'" class="block truncate" x-text="role.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <input type="hidden" name="role" :value="selected">
                @error('role') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password"
                       class="block w-full py-2.5 mt-1 border-gray-300 rounded-lg shadow-sm focus:border-[#0777be] focus:ring-[#0777be] sm:text-sm @error('password') border-red-500 @enderror"
                       @if(!isset($user)) required @endif>

                <p class="mt-1 text-xs text-gray-500">{{ isset($user) ? 'Leave blank to keep current password.' : 'Min 8 chars required.' }}</p>
                @error('password') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Status & Verification --}}
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h3 class="mb-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Account Status</h3>

            {{-- Active Status Toggle --}}
            <div x-data="{ on: {{ old('is_active', $user->is_active ?? 1) == 1 ? 'true' : 'false' }} }" class="flex items-center justify-between mb-6">
                <span class="flex flex-col flex-grow">
                    <span class="text-sm font-medium text-gray-900">Active Account</span>
                    <span class="text-xs text-gray-500">Enable or disable login access.</span>
                </span>
                <button type="button" @click="on = !on"
                        :class="{ 'bg-[#94c940]': on, 'bg-gray-200': !on }"
                        class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none">
                    <span aria-hidden="true" :class="{ 'translate-x-5': on, 'translate-x-0': !on }"
                          class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"></span>
                </button>
                <input type="hidden" name="is_active" :value="on ? 1 : 0">
            </div>

            <hr class="mb-6 border-gray-100">

            {{-- Email Verification Toggle --}}
            <div x-data="{ verified: {{ $isVerified ? 'true' : 'false' }} }" class="flex items-center justify-between">
                <span class="flex flex-col flex-grow">
                    <span class="text-sm font-medium text-gray-900">Email Verified</span>
                    <span class="text-xs text-gray-500">Mark email as verified manually.</span>
                </span>
                <button type="button" @click="verified = !verified"
                        :class="{ 'bg-[#0777be]': verified, 'bg-gray-200': !verified }"
                        class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none">
                    <span aria-hidden="true" :class="{ 'translate-x-5': verified, 'translate-x-0': !verified }"
                          class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"></span>
                </button>
                <input type="hidden" name="verify_email" :value="verified ? 1 : 0">
            </div>
        </div>
    </div>
</div>

{{-- Bottom Actions --}}
<div class="flex justify-end pt-6 mt-8 border-t border-gray-200 gap-x-4">
    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">Cancel</a>
    <button type="submit" class="inline-flex justify-center px-5 py-2.5 text-sm font-medium text-white bg-[#0777be] border border-transparent rounded-lg shadow-sm hover:bg-[#0777be]/90 focus:outline-none focus:ring-2 focus:ring-[#0777be] focus:ring-offset-2">
        {{ isset($user) ? 'Save Changes' : 'Create User' }}
    </button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('groupSelector', (config) => ({
            allGroups: config.allGroups,
            selectedIds: config.selectedIds,
            search: '',
            isOpen: false,
            activeIndex: -1,
            get selectedGroups() { return this.allGroups.filter(g => this.selectedIds.includes(g.id)); },
            get filteredGroups() {
                let available = this.allGroups.filter(g => !this.selectedIds.includes(g.id));
                if (this.search === '') return available;
                return available.filter(g => g.name.toLowerCase().includes(this.search.toLowerCase()));
            },
            openDropdown() { this.isOpen = true; this.activeIndex = -1; this.$nextTick(() => this.$refs.searchInput.focus()); },
            closeDropdown() { this.isOpen = false; this.activeIndex = -1; },
            addGroup(group) { if (!this.selectedIds.includes(group.id)) { this.selectedIds.push(group.id); } this.search = ''; this.$refs.searchInput.focus(); },
            removeGroup(id) { this.selectedIds = this.selectedIds.filter(gId => gId !== id); },
            highlightNext() { if (this.activeIndex < this.filteredGroups.length - 1) this.activeIndex++; },
            highlightPrev() { if (this.activeIndex > 0) this.activeIndex--; },
            selectHighlighted() { if (this.activeIndex >= 0 && this.filteredGroups[this.activeIndex]) { this.addGroup(this.filteredGroups[this.activeIndex]); } }
        }));

        Alpine.data('roleSelector', (config) => ({
            selected: config.selected,
            roles: config.roles,
            open: false,
            get displayValue() {
                if (!this.selected) return 'Select Role';
                let role = this.roles.find(r => r.value === this.selected);
                return role ? role.label : this.selected;
            },
            selectRole(value) { this.selected = value; this.open = false; }
        }));
    });
</script>
