@extends('layouts.site')

@section('content')
<div class="pt-32 pb-20 bg-slate-50 min-h-screen">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-extrabold text-slate-900">Checkout</h1>
            <p class="mt-2 text-slate-500">Securely finalize your subscription</p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative">
                <strong class="font-bold">Please check the form:</strong>
                <ul class="list-disc list-inside mt-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- LEFT COLUMN: Billing Form --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">1</span>
                        Billing Information
                    </h3>

                    <form id="checkout-form" action="{{ route('process_checkout', $plan->code) }}" method="POST">
                        @csrf

                        {{-- Read-Only User Info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                                <input type="text" name="full_name" value="{{ $user->name }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed focus:outline-none" readonly>
                            </div>
                        </div>

                        <div class="w-full h-px bg-slate-100 mb-6"></div>

                        {{-- Address Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Address <span class="text-red-500">*</span></label>
                                <input type="text" name="address" value="{{ old('address', $billing_information['address'] ?? '') }}" placeholder="Street address, Apartment, etc." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone', $billing_information['phone'] ?? $user->phone ?? '') }}" placeholder="Enter phone number" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Country <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="country" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none appearance-none cursor-pointer" required>
                                        <option value="" disabled selected>Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country }}"
                                                {{ (old('country', $billing_information['country'] ?? '') == $country) ? 'selected' : ($country == 'India' && !old('country') && empty($billing_information['country']) ? 'selected' : '') }}>
                                                {{ $country }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">State / Province <span class="text-red-500">*</span></label>
                                <input type="text" name="state" value="{{ old('state', $billing_information['state'] ?? '') }}" placeholder="e.g. Maharashtra" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" required>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">City <span class="text-red-500">*</span></label>
                                    <input type="text" name="city" value="{{ old('city', $billing_information['city'] ?? '') }}" placeholder="City" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Postal Code <span class="text-red-500">*</span></label>
                                    <input type="text" name="zip" value="{{ old('zip', $billing_information['zip'] ?? '') }}" placeholder="Zip Code" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT COLUMN: Detailed Order Summary --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200 border border-slate-100 p-8 sticky top-24">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-sm font-bold">2</span>
                        Order Summary
                    </h3>

                    {{-- Plan Item --}}
                    <div class="flex items-start gap-4 mb-6 pb-6 border-b border-dashed border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                            🎓
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 leading-tight">{{ $order['plan_name'] }}</h4>
                            <p class="text-sm text-slate-500 mt-1">{{ $order['duration'] }} Months Access</p>
                        </div>
                    </div>

                    {{-- Price Breakdown --}}
                    <div class="space-y-3 mb-6">
                        {{-- Original Price (if discounted) --}}
                        @if(isset($order['has_discount']) && $order['has_discount'])
                            <div class="flex justify-between items-center text-slate-500">
                                <span class="text-sm">Original Price</span>
                                <span class="text-sm line-through decoration-red-400 decoration-2">
                                    {{ $order['currency_symbol'] }}{{ number_format($order['original_price'], 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-green-600">
                                <span class="text-sm font-medium">Discount Applied</span>
                                <span class="text-sm font-bold">
                                    - {{ $order['currency_symbol'] }}{{ number_format($order['discount_amount'], 2) }}
                                </span>
                            </div>
                        @endif

                        {{-- Subtotal --}}
                        <div class="flex justify-between items-center text-slate-700">
                            <span class="font-bold">Subtotal</span>
                            <span class="font-bold">{{ $order['currency_symbol'] }}{{ number_format($order['sub_total'], 2) }}</span>
                        </div>

                        {{-- Taxes --}}
                        @foreach($order['taxes'] as $tax)
                            <div class="flex justify-between items-center text-slate-500 text-sm">
                                <span>{{ $tax['name'] }}</span>
                                <span>+ {{ $order['currency_symbol'] }}{{ number_format($tax['amount'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total Divider --}}
                    <div class="w-full h-px bg-slate-200 mb-6"></div>

                    {{-- Final Total --}}
                    <div class="flex justify-between items-center mb-8">
                        <span class="text-lg font-bold text-slate-800">Total Payable</span>
                        <span class="text-3xl font-extrabold text-[var(--brand-blue)]">
                            {{ $order['currency_symbol'] }}{{ number_format($order['total'], 2) }}
                        </span>
                    </div>

                    {{-- Pay Button --}}
                    <button onclick="document.getElementById('checkout-form').submit();"
                            class="w-full py-4 rounded-xl text-white font-bold text-lg shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-1 hover:shadow-xl active:scale-95"
                            style="background: linear-gradient(to right, var(--brand-blue), #2563eb);">
                        Pay Securely &rarr;
                    </button>

                    {{-- Security Badge --}}
                    <div class="mt-6 flex flex-col items-center gap-2 text-center">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span>100% Secure Payment</span>
                        </div>
                        <p class="text-[10px] text-slate-400">
                            Processed by Razorpay. Your data is encrypted.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
