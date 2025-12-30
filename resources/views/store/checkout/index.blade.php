@extends('layouts.site')

@section('content')
<div class="pt-32 pb-20 bg-slate-50 min-h-screen">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        <div class="mb-10 text-center">
            <h1 class="text-3xl font-extrabold text-slate-900">Checkout</h1>
            <p class="mt-2 text-slate-500">Review your order and complete purchase</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-8">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">1</span>
                        Billing Details
                    </h3>

                    <form id="checkout-form" action="{{ route('process_checkout', $plan->code) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                                <input type="text" value="{{ $user->name }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                                <input type="email" value="{{ $user->email }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 focus:outline-none" readonly>
                            </div>
                            <input type="hidden" name="payment_method" value="razorpay">
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 sticky top-24">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-sm">2</span>
                        Order Summary
                    </h3>

                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-start pb-4 border-b border-slate-100">
                            <div>
                                <p class="font-bold text-slate-800">{{ $order['plan_name'] }}</p>
                                <p class="text-sm text-slate-500">Subscription</p>
                            </div>
                            <p class="font-bold text-slate-900">{{ $order['currency_symbol'] }}{{ $order['sub_total'] }}</p>
                        </div>

                        @foreach($order['taxes'] as $tax)
                            <div class="flex justify-between items-center text-sm text-slate-600">
                                <p>{{ $tax['name'] }}</p>
                                <p>+ {{ $order['currency_symbol'] }}{{ $tax['amount'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-slate-100 mb-8">
                        <p class="text-lg font-bold text-slate-900">Total to Pay</p>
                        <p class="text-2xl font-extrabold text-blue-600">{{ $order['currency_symbol'] }}{{ $order['total'] }}</p>
                    </div>

                    <button onclick="document.getElementById('checkout-form').submit();"
                            class="w-full py-4 rounded-xl text-white font-bold text-lg shadow-lg shadow-blue-500/30 transition-transform hover:-translate-y-1"
                            style="background: linear-gradient(to right, var(--brand-blue), #2563eb);">
                        Pay Securely
                    </button>

                    <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        <span>256-bit SSL Secure Payment</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
