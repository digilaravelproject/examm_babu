@extends('layouts.admin')

@section('title', 'Referral History')

@section('content')

{{-- 1. Brand Variables & Compact Styles --}}
<style>
    /* Brand Color Utilities */
    .text-b-blue { color: var(--brand-blue); }
    .bg-b-blue { background-color: var(--brand-blue); }
    .border-b-blue { border-color: var(--brand-blue); }

    .text-b-green { color: var(--brand-green); }
    .bg-b-green { background-color: var(--brand-green); }

    .text-b-sky { color: var(--brand-sky); }
    .bg-b-sky { background-color: var(--brand-sky); }

    .bg-b-sidebar { background-color: var(--sidebar-bg); }

    /* Compact Table Spacing */
    .table-compact th, .table-compact td {
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }
</style>

<div class="w-full px-4 py-6 mx-auto">

    {{-- 2. Header Section --}}
    <div class="flex flex-col items-start justify-between gap-4 mb-6 md:flex-row md:items-center">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Referral History</h2>
            <p class="text-xs text-slate-500">Track referral commissions and earnings.</p>
        </div>
        <div>
            {{-- Export Button (Clean Look) --}}
            {{-- <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold transition-all bg-white border rounded-lg border-slate-300 text-slate-600 hover:bg-slate-50 hover:text-b-blue hover:border-b-blue shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Report
            </button> --}}
        </div>
    </div>

    {{-- 3. Compact Data Table Card --}}
    <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-xl">

        {{-- Card Header --}}
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
            <h6 class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-b-blue">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Commission Logs
            </h6>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left table-compact">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                    <tr>
                        <th class="px-5">ID</th>
                        <th class="px-5">Referrer (Instructor)</th>
                        <th class="px-5">Referee (Student)</th>
                        <th class="px-5">Plan Purchased</th>
                        <th class="px-5 text-right">Comm. Earned</th>
                        <th class="px-5 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px] text-slate-600">
                    @forelse($referrals as $ref)
                    <tr class="transition-colors hover:bg-slate-50">

                        {{-- ID --}}
                        <td class="px-5 font-mono text-xs font-semibold text-slate-400">
                            #{{ $ref->id }}
                        </td>

                        {{-- Referrer Column --}}
                        <td class="px-5">
                            <div class="flex items-center gap-3">
                                {{-- Avatar --}}
                                <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full bg-b-sidebar">
                                    {{ strtoupper(substr($ref->referrer?->full_name ?? 'U', 0, 1)) }}
                                </div>
                                {{-- Details --}}
                                <div class="leading-tight">
                                    <div class="font-bold text-slate-800">{{ $ref->referrer?->full_name ?? 'Unknown' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $ref->referrer?->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Referee Column --}}
                        <td class="px-5">
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-700">{{ $ref->referee?->full_name ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400">Student</span>
                            </div>
                        </td>

                        {{-- Plan Details --}}
                        <td class="px-5">
                            @if($ref->payment && $ref->payment->plan)
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border border-sky-100 bg-sky-50 text-b-blue">
                                        {{ $ref->payment->plan->name }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Amt: ₹{{ number_format($ref->plan_amount, 2) }}</span>
                                </div>
                            @else
                                <span class="px-2 py-1 text-xs rounded text-slate-500 bg-slate-100">Unknown Plan</span>
                            @endif
                        </td>

                        {{-- Commission Column --}}
                        <td class="px-5 text-right">
                            <div class="font-bold text-b-green text-[13px]">
                                +₹{{ number_format($ref->commission_amount, 2) }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Rate: {{ $ref->commission_percentage }}%
                            </div>
                        </td>

                        {{-- Date Column --}}
                        <td class="px-5 text-right">
                            <div class="text-slate-600 text-[12px] font-medium">{{ $ref->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $ref->created_at->format('h:i A') }}</div>
                        </td>
                    </tr>
                    @empty

                    {{-- Empty State --}}
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-3 mb-2 rounded-full bg-slate-50">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <h6 class="text-sm font-bold text-slate-600">No referral records found</h6>
                                <p class="text-xs">Once instructors refer students, data will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer / Pagination --}}
        @if($referrals->hasPages())
        <div class="px-4 py-3 border-t bg-slate-50 border-slate-100">
            <div class="flex justify-end">
                {{ $referrals->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
