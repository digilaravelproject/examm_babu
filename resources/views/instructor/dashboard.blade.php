@extends('layouts.instructor')

@section('header', 'Dashboard')

@section('content')

{{-- 🔥 DIRECT DATABASE CALL IN VIEW (AS REQUESTED) --}}
@php
    $currentRole = request()->route('role');

    // 1. Fetch Global Settings directly
    $globalSettings = app(\App\Settings\ReferralSettings::class);

    // 2. Fetch User Custom Settings directly
    // Hum Auth::user() ko reload kar rahe hain taaki relation pakka load ho jaye
    $currentUser = \App\Models\User::with('referralSetting')->find(Auth::id());

    // 3. Logic: Agar User ka Custom Rate hai to wo lo, nahi to Global Rate lo
    $commissionRate = $currentUser->referralSetting->commission_percentage ?? $globalSettings->commission_percentage ?? 0;

    // Ensure Float
    $commissionRate = (float) $commissionRate;
@endphp

<style>
    .btn-brand-primary { background-color: var(--brand-blue); color: white; transition: 0.2s; }
    .btn-brand-primary:hover { background-color: #055c93; box-shadow: 0 4px 12px rgba(7, 119, 190, 0.3); }
    [x-cloak] { display: none !important; }
</style>

<div class="mx-auto space-y-8 max-w-7xl" x-data="{ showWithdrawModal: false }">

    {{-- 1. WELCOME & WALLET SECTION --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        {{-- Welcome & Quick Actions --}}
        <div class="relative p-6 overflow-hidden bg-white border shadow-sm md:col-span-2 rounded-2xl border-slate-200">
            <div class="relative z-10">
                <h1 class="text-2xl font-extrabold text-slate-800">Welcome, {{ Auth::user()->name }}! 👋</h1>
                <p class="mt-2 text-slate-500">You have <span class="font-bold text-slate-800">{{ $totalReferrals ?? 0 }} referrals</span> this month.</p>

                <div class="flex flex-wrap gap-3 mt-6">
                    {{-- Create Question --}}
                    <a href="{{ route('panel.questions.create', ['role' => $currentRole]) }}"
                       class="flex items-center gap-2 px-4 py-2 font-bold transition rounded-lg bg-blue-50 text-brand-blue hover:bg-blue-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create Question
                    </a>

                    {{-- Copy Referral Link --}}
                    <button x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText('{{ url('/?ref='. (Auth::user()->referral_code ?? 'CODE')) }}'); copied = true; setTimeout(() => copied = false, 2000);"
                            class="flex items-center gap-2 px-4 py-2 font-bold text-gray-700 transition bg-gray-100 rounded-lg hover:bg-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <span x-text="copied ? 'Link Copied!' : 'Copy Referral Link'"></span>
                    </button>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-32 h-32 -mt-4 -mr-4 rounded-full bg-brand-blue opacity-10 blur-2xl"></div>
        </div>

        {{-- Wallet Summary Card --}}
        <div class="relative flex flex-col justify-between p-6 overflow-hidden text-white shadow-lg bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl">
            <div class="relative z-10">
                <p class="text-xs font-bold tracking-wider uppercase text-slate-400">Available Balance</p>
                <h2 class="mt-1 text-3xl font-bold">₹{{ number_format($totalEarnings ?? 0, 2) }}</h2>
                <div class="flex items-center gap-2 mt-4 text-xs text-slate-300">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>Ready for payout</span>
                </div>
            </div>
            <div class="relative z-10 mt-6">
                <a href="{{ route('panel.referral.dashboard', ['role' => $currentRole]) }}"
                   class="block w-full py-2 text-sm font-semibold text-center transition rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm">
                    View Referral History
                </a>
            </div>
            <svg class="absolute bottom-0 right-0 w-24 h-24 text-white transform translate-x-4 translate-y-4 opacity-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </div>
    </div>

    {{-- 2. CONTENT STATISTICS --}}
    <div>
        <h3 class="mb-4 text-lg font-bold text-slate-800">Content Analytics</h3>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Questions Stats --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Questions Created</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800">{{ $questionsCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 text-blue-600 rounded-lg bg-blue-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Exam Stats --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Exams / Tests</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800">{{ $examsCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 text-purple-600 rounded-lg bg-purple-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Plans / Sales --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Plans Sold</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800">{{ $totalReferrals ?? 0 }}</p>
                    </div>
                    <div class="p-3 text-green-600 rounded-lg bg-green-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Pending Actions --}}
            <div class="p-5 transition-shadow bg-white border border-gray-100 shadow-sm rounded-xl hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Pending Approval</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800">{{ $pendingQuestions ?? 0 }}</p>
                    </div>
                    <div class="p-3 text-yellow-600 rounded-lg bg-yellow-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. PROMOTION AREA --}}
    @if(isset($plans) && $plans->count() > 0)
    <div class="p-6 bg-white border shadow-sm rounded-xl border-slate-200">
        <h3 class="mb-4 text-lg font-bold text-slate-900">Promote Plans</h3>
        
        {{-- 🔥 Yaha ab sahi rate dikhega --}}
        <p class="mb-6 text-sm text-slate-500">Use these direct links to sell plans to students and earn <span class="font-bold text-brand-blue">{{ $commissionRate }}%</span> commission instantly.</p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
             @foreach($plans as $plan)
                <div class="p-4 transition border rounded-lg cursor-pointer border-slate-200 hover:border-brand-blue group">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-slate-800">{{ $plan->name }}</h4>
                            <p class="text-sm text-slate-500">₹{{ $plan->price }}</p>
                        </div>
                        
                        {{-- 🔥 Calculation Fix with Direct PHP Variable --}}
                        <span class="bg-blue-50 text-brand-blue text-[10px] font-bold px-2 py-1 rounded">
                            Earn ₹{{ number_format($plan->price * ($commissionRate / 100), 2) }}
                        </span>
                    </div>
                    
                    <button class="w-full py-2 mt-4 text-xs font-bold transition rounded text-slate-600 bg-slate-50 group-hover:bg-brand-blue group-hover:text-white"
                        onclick="navigator.clipboard.writeText('{{ url('/checkout/' . $plan->code) . '?ref=' . (Auth::user()->referral_code ?? 'CODE') }}'); alert('Checkout link copied!');">
                        Copy Link
                    </button>
                </div>
             @endforeach
        </div>
    </div>
    @endif

</div>
@endsection