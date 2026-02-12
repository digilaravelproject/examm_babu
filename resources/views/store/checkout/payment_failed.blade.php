@extends('layouts.site')

@section('content')
<div class="flex items-center justify-center min-h-screen px-4 bg-slate-50">
    <div class="relative w-full max-w-sm p-8 overflow-hidden text-center bg-white border border-red-100 shadow-xl rounded-3xl">

        <div class="absolute top-0 left-0 w-full h-1.5 bg-red-500"></div>
        <div class="absolute w-24 h-24 rounded-full -top-10 -left-10 bg-red-50 blur-xl opacity-70"></div>

        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 rounded-full bg-red-50 ring-4 ring-red-50">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h2 class="mb-2 text-2xl font-bold text-slate-800">Payment Failed</h2>
        <p class="mb-6 text-sm leading-relaxed text-slate-500">
            Oops! The transaction couldn't be completed. Please check your payment details or try a different card.
        </p>

        <div class="space-y-3">
            <a href="{{ url()->previous() }}"
               class="block w-full py-3 rounded-xl text-white text-sm font-semibold shadow-md shadow-red-200 transition-all hover:shadow-lg hover:-translate-y-0.5"
               style="background-color: var(--danger, #ef4444);">
                Try Again
            </a>

            <a href="{{ route('welcome') }}"
               class="block w-full py-3 text-sm font-semibold transition-colors text-slate-400 hover:text-slate-600">
                Cancel Transaction
            </a>
        </div>
    </div>
</div>
@endsection
