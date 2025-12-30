@extends('layouts.site')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-slate-50 pt-20 pb-20">
    <div class="bg-white p-12 rounded-[2.5rem] shadow-2xl text-center max-w-lg w-full border border-red-50 relative overflow-hidden">

        <div class="absolute top-0 left-0 w-full h-2 bg-red-500"></div>
        <div class="absolute -top-10 -left-10 w-32 h-32 bg-red-50 rounded-full blur-2xl"></div>

        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
            <svg class="w-12 h-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Payment Failed</h2>
        <p class="text-slate-500 text-lg mb-8 leading-relaxed">
            Oops! Something went wrong with the transaction. Please check your details or try a different payment method.
        </p>

        <div class="space-y-4">
            <a href="{{ url()->previous() }}"
               class="block w-full py-4 rounded-xl text-white font-bold shadow-lg shadow-red-500/30 transition-transform hover:-translate-y-1"
               style="background-color: var(--danger);">
                Try Again
            </a>
            <a href="{{ route('welcome') }}" class="block w-full py-4 text-slate-500 font-bold hover:text-slate-800 transition-colors">
                Cancel
            </a>
        </div>
    </div>
</div>
@endsection
