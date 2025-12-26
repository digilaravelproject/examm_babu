@extends('layouts.admin')
@section('title', 'New Subscription')

@section('content')
<div class="flex justify-center items-start pt-6 h-full">

    {{-- Card Container --}}
    <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden border border-slate-200">

        {{-- 1. Dark Header (Matches Sidebar) --}}
        <div class="bg-[#0f172a] px-8 py-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-white tracking-wide">Create Subscription</h2>
                <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider">Assign Plan Manually</p>
            </div>
            {{-- Back Icon --}}
            <a href="{{ route('admin.subscriptions.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </a>
        </div>

        {{-- 2. Form Body --}}
        <div class="p-8">
            <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- User Selection (Alpine Searchable) --}}
                <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        selectedName: 'Select User'
                    }" class="relative">

                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                        Select User <span class="text-red-500">*</span>
                    </label>

                    <input type="hidden" name="user_id" :value="selectedId" required>

                    {{-- Trigger --}}
                    <button type="button" @click="open = !open; $nextTick(() => $refs.searchInput.focus())"
                        class="w-full bg-slate-50 border border-slate-200 text-left rounded-lg px-4 py-3 text-sm font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f172a] flex justify-between items-center hover:bg-white transition-colors">
                        <span x-text="selectedName" :class="selectedId ? 'text-slate-900' : 'text-slate-500'"></span>
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open" @click.away="open = false" style="display: none;"
                        class="absolute z-20 mt-1 w-full bg-white shadow-xl max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">

                        <div class="sticky top-0 z-10 bg-white px-2 py-2 border-b border-slate-100">
                            <input x-ref="searchInput" x-model="search" type="text"
                                class="w-full border-slate-200 bg-slate-50 rounded-md text-sm p-2 focus:ring-[#0f172a] focus:border-[#0f172a]"
                                placeholder="Search user...">
                        </div>

                        <ul class="py-1">
                            @foreach ($users as $user)
                                <li x-show="'{{ strtolower(($user->first_name ?? $user->name) . ' ' . ($user->last_name ?? '') . ' ' . $user->email) }}'.includes(search.toLowerCase())"
                                    @click="selectedId = '{{ $user->id }}'; selectedName = '{{ $user->first_name ?? $user->name }} {{ $user->last_name ?? '' }} ({{ $user->email }})'; open = false"
                                    class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-slate-50 text-slate-900 border-b border-slate-50 last:border-0">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-[#0f172a]">
                                            {{ $user->first_name ?? $user->name }} {{ $user->last_name ?? '' }}
                                        </span>
                                        <span class="text-xs text-slate-500">{{ $user->email }}</span>
                                    </div>
                                </li>
                            @endforeach
                            <li x-show="$el.parentNode.querySelectorAll('li[x-show]:not([style*=\'display: none\'])').length === 0" class="py-3 pl-4 text-slate-400 text-sm italic">
                                No users found.
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Plan Selection --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                        Select Plan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="plan_id" required
                                class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#0f172a] focus:bg-white appearance-none transition-colors">
                            <option value="">Choose a Plan...</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} ({{ $plan->duration }} Months) - {{ $plan->price }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                        Initial Status
                    </label>
                    <div class="relative">
                        <select name="status"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#0f172a] focus:bg-white appearance-none transition-colors">
                            <option value="active">Active (Start Now)</option>
                            <option value="created">Created (Pending Payment)</option>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button type="submit"
                            class="w-full bg-[#0f172a] text-white font-bold rounded-lg py-3.5 shadow-lg hover:bg-[#1e293b] hover:shadow-xl focus:ring-4 focus:ring-slate-300 transition-all transform hover:-translate-y-0.5 uppercase tracking-wide text-sm">
                        Create Subscription
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
