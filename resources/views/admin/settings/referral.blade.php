@extends('layouts.admin')

{{-- Header Title --}}
@section('header', 'Referral Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header Text --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Referral Configuration</h1>
        <p class="mt-1 text-sm text-gray-500">Manage commission rates, recurring rewards, and security limits.</p>
    </div>

    {{-- Main Card --}}
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">

        {{-- Card Header --}}
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <div class="p-2 bg-blue-100 rounded-lg text-[#0777be]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="font-bold text-gray-800">System Settings</h3>
        </div>

        <div class="p-6">
            {{-- Success Message --}}
            @if(session('success'))
                <div class="flex items-center gap-2 p-4 mb-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.settings.referral.update') }}" method="POST">
                @csrf

                <div class="space-y-8">

                    {{-- 1. Enable/Disable Switch --}}
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl bg-gray-50/50">
                        <div>
                            <label for="enableReferral" class="block font-bold text-gray-800 cursor-pointer">Enable Referral System</label>
                            <p class="mt-1 text-xs text-gray-500">If disabled, no new commissions will be credited.</p>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="enableReferral" name="enable_referral" class="sr-only peer" {{ $settings->enable_referral ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- 2. Commission Strategy Section --}}
                    <div>
                        <h4 class="mb-4 text-sm font-bold tracking-wider text-gray-400 uppercase">Commission Strategy</h4>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- New User Commission --}}
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-700">New User Commission (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="commission_percentage"
                                           value="{{ $settings->commission_percentage }}" required
                                           class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0777be] focus:border-[#0777be] outline-none transition-all placeholder-gray-400">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="font-bold text-gray-500">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Reward for bringing a brand new student.</p>
                            </div>

                            {{-- Recurring Commission (NEW) --}}
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-700">Recurring/Existing User (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="recurring_commission_percentage"
                                           value="{{ $settings->recurring_commission_percentage }}" required
                                           class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0777be] focus:border-[#0777be] outline-none transition-all placeholder-gray-400 bg-blue-50/30">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="font-bold text-gray-500">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Reward when an existing student buys a new exam.</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- 3. Limits & Security Section --}}
                    <div>
                        <h4 class="mb-4 text-sm font-bold tracking-wider text-gray-400 uppercase">Limits & Security</h4>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- Withdrawal Limit --}}
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-700">Minimum Withdrawal Limit</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="font-bold text-gray-500">₹</span>
                                    </div>
                                    <input type="number" step="1" name="min_withdrawal_amount"
                                           value="{{ $settings->min_withdrawal_amount }}" required
                                           class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0777be] focus:border-[#0777be] outline-none transition-all placeholder-gray-400">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Minimum balance required for payout request.</p>
                            </div>

                            {{-- Cookie Lifetime --}}
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-700">Referral Cookie Validity</label>
                                <div class="relative">
                                    <input type="number" step="1" name="cookie_lifetime_days"
                                           value="{{ $settings->cookie_lifetime_days ?? 30 }}" required
                                           class="w-full pl-4 pr-16 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0777be] focus:border-[#0777be] outline-none transition-all placeholder-gray-400">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-sm font-bold text-gray-500">Days</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">How long the referral link tracks the user.</p>
                            </div>

                            {{-- Spam Protection (NEW) --}}
                            <div class="md:col-span-2">
                                <div class="p-4 border border-red-100 rounded-xl bg-red-50/30">
                                    <label class="block mb-2 text-sm font-bold text-gray-700">Spam Protection Cool-down</label>
                                    <div class="flex items-center gap-4">
                                        <div class="relative w-full md:w-1/2">
                                            <input type="number" step="1" name="spam_protection_days"
                                                   value="{{ $settings->spam_protection_days ?? 1 }}" required
                                                   class="w-full pl-4 pr-16 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <span class="text-sm font-bold text-gray-500">Days</span>
                                            </div>
                                        </div>
                                        <p class="flex-1 text-xs text-gray-500">
                                            <strong>How it works:</strong> Once an instructor earns a commission from Student A, they cannot earn another commission from the <u>same student</u> for this many days. <br>
                                            (Recommended: 1 to 7 Days).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end pt-6 mt-8 border-t border-gray-100">
                    <button type="submit" class="flex items-center px-6 py-2.5 text-sm font-bold text-white transition-all rounded-lg shadow-md bg-[#0777be] hover:bg-[#055a91] hover:shadow-lg focus:ring-2 focus:ring-offset-2 focus:ring-[#0777be]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Configuration
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
