@extends('layouts.admin')
@section('title', 'Payment Settings')
@section('content')

    @include('admin.settings._nav')

    <div class="space-y-6">

        {{-- Currency Settings --}}
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Currency Settings</h2>
            <form action="{{ route('admin.settings.update-payment') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency Code</label>
                        <select name="default_currency"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <option value="INR" {{ $payment->default_currency == 'INR' ? 'selected' : '' }}>INR (Rupee)
                            </option>
                            <option value="USD" {{ $payment->default_currency == 'USD' ? 'selected' : '' }}>USD (Dollar)
                            </option>
                            <option value="EUR" {{ $payment->default_currency == 'EUR' ? 'selected' : '' }}>EUR (Euro)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="{{ $payment->currency_symbol }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Symbol Position</label>
                        <select name="currency_symbol_position"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <option value="left" {{ $payment->currency_symbol_position == 'left' ? 'selected' : '' }}>Left
                                ($100)</option>
                            <option value="right" {{ $payment->currency_symbol_position == 'right' ? 'selected' : '' }}>
                                Right (100$)</option>
                            <option value="left_space"
                                {{ $payment->currency_symbol_position == 'left_space' ? 'selected' : '' }}>Left with Space
                                ($ 100)</option>
                            <option value="right_space"
                                {{ $payment->currency_symbol_position == 'right_space' ? 'selected' : '' }}>Right with Space
                                (100 $)</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="text-[#0777be] font-semibold text-sm hover:underline">Update
                        Currency</button>
                </div>
            </form>
        </div>

        {{-- Razorpay Settings --}}
        <div class="bg-white border border-[#0777be]/30 shadow-md shadow-[#0777be]/5 rounded-xl p-6">
            <div class="flex items-center gap-3 pb-2 mb-4 border-b">
                <div class="w-8 h-8 rounded-full bg-[#0777be]/10 flex items-center justify-center text-[#0777be]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Razorpay Integration</h2>
            </div>

            <form action="{{ route('admin.settings.update-razorpay') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Key ID</label>
                        <input type="text" name="key_id" value="{{ $razorpay->key_id }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Key Secret</label>
                        <input type="password" name="key_secret" value="{{ $razorpay->key_secret }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Webhook Secret (Optional)</label>
                        <input type="text" name="webhook_secret" value="{{ $razorpay->webhook_secret }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="bg-[#0777be] text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:bg-[#0777be]/90 transition">
                        Save Razorpay Settings
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
