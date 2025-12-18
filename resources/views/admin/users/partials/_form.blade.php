@php
    // Prepare Data for AlpineJS
    // Groups Data
    $groupsData = $userGroups->map(function($group) {
        return ['id' => $group->id, 'name' => $group->name];
    });

    // Check old input or DB for selected groups
    $currentGroups = collect(old('user_groups', isset($selectedGroups) ? $selectedGroups : []))->map(function($id){
        return (int)$id;
    });

    // Roles Data for JS
    // Ensure roles are formatted as array of objects for easier JS handling
    // Assuming $roles is an array/collection of names like ['admin', 'instructor']
    $rolesData = collect($roles)->map(function($role) {
        return ['value' => $role, 'label' => ucfirst($role)];
    })->values();

    $currentRole = old('role', $userRole ?? '');

    // Email Verified Logic
    $isVerified = old('verify_email') ? true : (isset($user) && $user->email_verified_at != null);
@endphp

<div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

    {{-- LEFT COLUMN: Basic Details (Span 8) --}}
    <div class="space-y-6 lg:col-span-8">

        {{-- Card: Personal Info --}}
        <div class="p-6 border border-gray-100 rounded-lg bg-gray-50">
            <h3 class="flex items-center mb-4 text-lg font-medium leading-6 text-gray-900">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Personal Information
            </h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- First Name --}}
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" id="first_name"
                        value="{{ old('first_name', $user->first_name ?? '') }}"
                        class="block w-full py-2 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required placeholder="John">
                    @error('first_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Last Name --}}
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name"
                        value="{{ old('last_name', $user->last_name ?? '') }}"
                        class="block w-full py-2 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Doe">
                    @error('last_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label for="user_name" class="block text-sm font-medium text-gray-700">Username <span class="text-red-500">*</span></label>
                    <div class="flex mt-1 rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 text-gray-500 border border-r-0 border-gray-300 rounded-l-md bg-gray-50 sm:text-sm">@</span>
                        <input type="text" name="user_name" id="user_name"
                            value="{{ old('user_name', $user->user_name ?? '') }}"
                            class="flex-1 block w-full min-w-0 py-2 border-gray-300 rounded-none rounded-r-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required placeholder="johndoe">
                    </div>
                    @error('user_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email"
                        value="{{ old('email', $user->email ?? '') }}"
                        class="block w-full py-2 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required placeholder="john@example.com">
                    @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Card: User Groups (Custom Multi Select - ENHANCED) --}}
        <div class="p-6 border border-gray-100 rounded-lg bg-gray-50"
             x-data="groupSelector({
                 allGroups: {{ $groupsData }},
                 selectedIds: {{ $currentGroups }}
             })"
             @click.outside="closeDropdown()">

            <h3 class="flex items-center mb-4 text-lg font-medium leading-6 text-gray-900">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                User Groups Assignment
            </h3>

            <label class="block mb-2 text-sm font-medium text-gray-700">Select Groups</label>

            <div class="relative">
                {{-- Selected Tags & Search Input Container --}}
                <div class="bg-white border border-gray-300 rounded-md p-2 flex flex-wrap gap-2 min-h-[42px] focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500 cursor-text shadow-sm"
                     @click="openDropdown()">

                    <template x-for="group in selectedGroups" :key="group.id">
                        <span class="inline-flex items-center py-0.5 pl-2.5 pr-1 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-full">
                            <span x-text="group.name"></span>
                            <button type="button" @click.stop="removeGroup(group.id)" class="inline-flex items-center justify-center flex-shrink-0 w-4 h-4 ml-0.5 text-indigo-400 rounded-full hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none">
                                <span class="sr-only">Remove</span>
                                <svg class="w-2 h-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                            </button>
                        </span>
                    </template>

                    {{-- Input Field --}}
                    <input type="text" x-ref="searchInput" x-model="search"
                           placeholder="Click to select or type to search..."
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
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute z-20 w-full py-1 mt-1 overflow-auto bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                     style="display: none;">

                    <template x-for="(group, index) in filteredGroups" :key="group.id">
                        <div @click="addGroup(group)"
                             @mouseenter="activeIndex = index"
                             :class="{ 'bg-indigo-600 text-white': activeIndex === index, 'text-gray-900': activeIndex !== index }"
                             class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none pr-9 hover:bg-indigo-600 hover:text-white">
                            <span x-text="group.name" class="block font-normal truncate"></span>

                            {{-- Checkmark if selected (Logic handled by filter, but kept for structure) --}}
                            <span x-show="selectedIds.includes(group.id)" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600" :class="{ 'text-white': activeIndex === index }">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                        </div>
                    </template>
                </div>

                {{-- No results state --}}
                <div x-show="isOpen && filteredGroups.length === 0" class="absolute z-20 w-full py-2 mt-1 overflow-hidden bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                   <p class="px-3 text-sm text-gray-500">No groups found.</p>
                </div>
            </div>

            {{-- Hidden Inputs for Form Submission --}}
            <template x-for="id in selectedIds">
                <input type="hidden" name="user_groups[]" :value="id">
            </template>
        </div>

    </div>

    {{-- RIGHT COLUMN: Security & Settings (Span 4) --}}
    <div class="space-y-6 lg:col-span-4">

        {{-- Role & Password Card --}}
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-500 uppercase">Security</h3>

            {{-- Custom Role Dropdown --}}
            <div class="mb-6"
                 x-data="roleSelector({
                     selected: '{{ $currentRole }}',
                     roles: {{ json_encode($rolesData) }}
                 })"
                 @click.outside="open = false">

                <label class="block mb-1 text-sm font-medium text-gray-700">User Role <span class="text-red-500">*</span></label>

                <div class="relative">
                    {{-- Trigger Button --}}
                    <button type="button"
                            @click="open = !open"
                            class="relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <span class="block truncate" x-text="displayValue"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>

                    {{-- Dropdown List --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute z-10 w-full py-1 mt-1 overflow-auto bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                         style="display: none;">

                        <template x-for="role in roles" :key="role.value">
                            <div @click="selectRole(role.value)"
                                 class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none pr-9 hover:bg-indigo-600 hover:text-white">
                                <span :class="selected === role.value ? 'font-semibold' : 'font-normal'" class="block truncate" x-text="role.label"></span>

                                <span x-show="selected === role.value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hover:text-white">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Hidden Input for form submission --}}
                <input type="hidden" name="role" :value="selected">
                @error('role') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                <input type="password" name="password" id="password"
                       class="block w-full py-2 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       @if(!isset($user)) required @endif>

                @if(isset($user))
                    <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password.</p>
                @else
                    <p class="mt-1 text-xs text-gray-500">Required for new accounts.</p>
                @endif

                @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Status & Verification Card (Toggles) --}}
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-500 uppercase">Account Status</h3>

            {{-- Active Toggle --}}
            <div x-data="{ on: {{ old('is_active', $user->is_active ?? 1) == 1 ? 'true' : 'false' }} }" class="flex items-center justify-between mb-6">
                <span class="flex flex-col flex-grow">
                    <span class="text-sm font-medium text-gray-900">Active Account</span>
                    <span class="text-sm text-gray-500">Can log in to the system.</span>
                </span>

                <button type="button" @click="on = !on"
                        :class="{ 'bg-indigo-600': on, 'bg-gray-200': !on }"
                        class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span aria-hidden="true"
                          :class="{ 'translate-x-5': on, 'translate-x-0': !on }"
                          class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"></span>
                </button>
                <input type="hidden" name="is_active" :value="on ? 1 : 0">
            </div>

            <hr class="mb-6 border-gray-100">

            {{-- Email Verified Toggle --}}
            <div x-data="{ verified: {{ $isVerified ? 'true' : 'false' }} }" class="flex items-center justify-between">
                <span class="flex flex-col flex-grow">
                    <span class="text-sm font-medium text-gray-900">Email Verified</span>
                    <span class="text-sm text-gray-500">Bypass email verification step.</span>
                </span>

                <button type="button" @click="verified = !verified"
                        :class="{ 'bg-green-500': verified, 'bg-gray-200': !verified }"
                        class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <span aria-hidden="true"
                          :class="{ 'translate-x-5': verified, 'translate-x-0': !verified }"
                          class="inline-block w-5 h-5 transition duration-200 ease-in-out transform bg-white rounded-full shadow pointer-events-none ring-0"></span>
                </button>
                <input type="hidden" name="verify_email" :value="verified ? 1 : 0">
            </div>
        </div>

    </div>
</div>

{{-- Form Actions --}}
<div class="flex justify-end pt-6 mt-8 border-t border-gray-200 gap-x-4">
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Cancel</a>
    <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        {{ isset($user) ? 'Save Changes' : 'Create User' }}
    </button>
</div>

{{-- ALPINE JS LOGIC --}}
<script>
    document.addEventListener('alpine:init', () => {

        // Logic for Multi-Select (User Groups)
        Alpine.data('groupSelector', (config) => ({
            allGroups: config.allGroups,
            selectedIds: config.selectedIds,
            search: '',
            isOpen: false,
            activeIndex: -1,

            get selectedGroups() {
                return this.allGroups.filter(g => this.selectedIds.includes(g.id));
            },

            // Updated Logic: Shows ALL groups if search is empty
            get filteredGroups() {
                // Remove already selected items from the list
                let available = this.allGroups.filter(g => !this.selectedIds.includes(g.id));

                if (this.search === '') {
                    return available; // Return ALL available if no search
                }

                return available.filter(g =>
                    g.name.toLowerCase().includes(this.search.toLowerCase())
                );
            },

            openDropdown() {
                this.isOpen = true;
                this.activeIndex = -1;
                this.$refs.searchInput.focus();
            },

            closeDropdown() {
                this.isOpen = false;
                this.activeIndex = -1;
            },

            addGroup(group) {
                if (!this.selectedIds.includes(group.id)) {
                    this.selectedIds.push(group.id);
                }
                this.search = '';
                this.$refs.searchInput.focus();
                // Keep dropdown open for multiple selection easier
                // If you want to close it, uncomment: this.closeDropdown();
            },

            removeGroup(id) {
                this.selectedIds = this.selectedIds.filter(gId => gId !== id);
            },

            // Keyboard Navigation
            highlightNext() {
                if (this.activeIndex < this.filteredGroups.length - 1) this.activeIndex++;
            },
            highlightPrev() {
                if (this.activeIndex > 0) this.activeIndex--;
            },
            selectHighlighted() {
                if (this.activeIndex >= 0 && this.filteredGroups[this.activeIndex]) {
                    this.addGroup(this.filteredGroups[this.activeIndex]);
                }
            }
        }));

        // Logic for Single Select (Roles)
        Alpine.data('roleSelector', (config) => ({
            selected: config.selected,
            roles: config.roles,
            open: false,

            get displayValue() {
                if (!this.selected) return 'Select Role';
                let role = this.roles.find(r => r.value === this.selected);
                return role ? role.label : this.selected;
            },

            selectRole(value) {
                this.selected = value;
                this.open = false;
            }
        }));
    });
</script>
