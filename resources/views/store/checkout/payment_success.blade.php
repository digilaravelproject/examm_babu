@extends('layouts.site')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-slate-50 pt-20 pb-20">
    <div class="bg-white p-12 rounded-[2.5rem] shadow-2xl text-center max-w-lg w-full border border-green-50 relative overflow-hidden">

        <div class="absolute top-0 left-0 w-full h-2 bg-green-500"></div>
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-50 rounded-full blur-2xl"></div>

        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
            <svg class="w-12 h-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Payment Successful!</h2>
        <p class="text-slate-500 text-lg mb-8 leading-relaxed">
            Thank you for your purchase. Your subscription is now active and you can access the content immediately.
        </p>

        <div class="space-y-4">
            <a href="{{ route('home') }}"
               class="block w-full py-4 rounded-xl text-white font-bold shadow-lg shadow-green-500/30 transition-transform hover:-translate-y-1"
               style="background-color: var(--brand-green);">
                Go to Dashboard
            </a>
            <a href="{{ route('welcome') }}" class="block w-full py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
