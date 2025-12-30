@extends('layouts.site')

@section('content')
<div class="min-h-screen pt-24 pb-12 bg-slate-50"> <div class="max-w-6xl px-4 mx-auto sm:px-6 lg:px-8"> {{-- Compact Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Checkout</h1>
                <p class="text-sm text-slate-500">Complete your purchase securely.</p>
            </div>
            <div class="hidden sm:block">
                <span class="px-3 py-1 text-xs font-semibold text-blue-700 border border-blue-100 rounded-full bg-blue-50">
                    SSL Encrypted Payment
                </span>
            </div>
        </div>

        {{-- Error Display --}}
        @if ($errors->any())
            <div class="p-3 mb-6 text-sm text-red-600 border border-red-200 rounded-lg bg-red-50">
                <strong class="font-bold">Please fix the errors:</strong>
                <ul class="mt-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-3"> {{-- LEFT COLUMN: Billing Form (Col Span 2) --}}
            <div class="lg:col-span-2">
                <div class="overflow-hidden bg-white border shadow-sm rounded-xl border-slate-200">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <h3 class="font-bold text-slate-700">Billing Information</h3>
                    </div>

                    <div class="p-6">
                        <form id="checkout-form" action="{{ route('process_checkout', $plan->code) }}" method="POST">
                            @csrf

                            {{-- Read-Only User Info (Compact Grid) --}}
                            <div class="grid grid-cols-1 gap-4 mb-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Full Name</label>
                                    <input type="text" name="full_name" value="{{ $user->fullname }}" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-200 bg-slate-100 text-slate-500 font-medium cursor-not-allowed focus:outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email Address</label>
                                    <input type="email" name="email" value="{{ $user->email }}" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-200 bg-slate-100 text-slate-500 font-medium cursor-not-allowed focus:outline-none" readonly>
                                </div>
                            </div>

                            <hr class="mb-5 border-slate-100">

                            {{-- Address Fields (Dense Grid) --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {{-- Address --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Address <span class="text-red-500">*</span></label>
                                    <input type="text" name="address" value="{{ old('address', $billing_information['address'] ?? '') }}" placeholder="House No, Street, Area" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" required>
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Phone Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone" value="{{ old('phone', $billing_information['phone'] ?? $user->phone ?? '') }}" placeholder="10-digit mobile" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" required>
                                </div>

                                {{-- Country Dropdown (Auto India) --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                                    <select name="country" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none cursor-pointer" required>
                                        <option value="" disabled>Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country }}"
                                                {{ (old('country', $billing_information['country'] ?? '') == $country) ? 'selected' : ($country == 'India' ? 'selected' : '') }}>
                                                {{ $country }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- State --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">State <span class="text-red-500">*</span></label>
                                    <input type="text" name="state" value="{{ old('state', $billing_information['state'] ?? '') }}" placeholder="e.g. Maharashtra" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" required>
                                </div>

                                {{-- City & Zip (Nested Grid for tight fit) --}}
                                <div class="grid grid-cols-2 gap-4 sm:col-span-1">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">City <span class="text-red-500">*</span></label>
                                        <input type="text" name="city" value="{{ old('city', $billing_information['city'] ?? '') }}" placeholder="City" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pincode <span class="text-red-500">*</span></label>
                                        <input type="text" name="zip" value="{{ old('zip', $billing_information['zip'] ?? '') }}" placeholder="Zip" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" required>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="payment_method" value="razorpay">
                        </form>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Order Summary (Col Span 1 - Sticky) --}}
            <div class="lg:col-span-1">
                <div class="sticky overflow-hidden bg-white border shadow-lg rounded-xl border-slate-200 top-24">
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-5 py-3 text-white bg-slate-900">
                        <h3 class="text-sm font-bold">Order Summary</h3>
                        <span class="text-xs bg-white/20 px-2 py-0.5 rounded text-white font-medium">Secured</span>
                    </div>

                    <div class="p-5">
                        {{-- Plan Info --}}
                        <div class="flex gap-3 mb-4">
                            <div class="flex items-center justify-center w-10 h-10 text-lg text-blue-600 rounded-lg bg-blue-50 shrink-0">
                                🎓
                            </div>
                            <div>
                                <h4 class="text-sm font-bold leading-snug text-slate-900">{{ $order['plan_name'] }}</h4>
                                <p class="text-xs text-slate-500">{{ $order['duration'] }} Months Plan</p>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="my-4 border-t border-dashed border-slate-200"></div>

                        {{-- Calculation --}}
                        <div class="space-y-2 text-sm">
                            {{-- Original Price --}}
                            @if(isset($order['has_discount']) && $order['has_discount'])
                                <div class="flex justify-between text-slate-400">
                                    <span>Price</span>
                                    <span class="line-through decoration-red-400">
                                        {{ $order['currency_symbol'] }}{{ number_format($order['original_price'], 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between font-medium text-green-600">
                                    <div class="flex items-center gap-1">
                                        <span>Discount</span>
                                        <span class="text-[10px] bg-green-100 px-1.5 rounded">{{ $order['discount_percentage'] }}%</span>
                                    </div>
                                    <span>- {{ $order['currency_symbol'] }}{{ number_format($order['discount_amount'], 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-slate-800">{{ $order['currency_symbol'] }}{{ number_format($order['sub_total'], 2) }}</span>
                            </div>

                            @foreach($order['taxes'] as $tax)
                                <div class="flex justify-between text-xs text-slate-500">
                                    <span>{{ $tax['name'] }}</span>
                                    <span>+ {{ $order['currency_symbol'] }}{{ number_format($tax['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total Bar --}}
                        <div class="flex items-center justify-between pt-3 mt-4 border-t border-slate-200">
                            <span class="font-bold text-slate-800">Total Payable</span>
                            <span class="text-xl font-extrabold text-[var(--brand-blue)]">
                                {{ $order['currency_symbol'] }}{{ number_format($order['total'], 2) }}
                            </span>
                        </div>

                        {{-- Checkout Button --}}
                        <button onclick="document.getElementById('checkout-form').submit();"
                                class="w-full mt-5 py-3 rounded-lg text-white font-bold text-sm shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 flex justify-center items-center gap-2"
                                style="background: var(--brand-blue);">
                            <span>Pay Securely</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </button>

                        <div class="mt-3 text-center">
                            <p class="text-[10px] text-slate-400">
                                By proceeding, you agree to our <a href="#" class="underline hover:text-blue-600">Terms</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
