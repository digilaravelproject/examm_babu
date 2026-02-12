@extends('layouts.admin')

@section('title', 'Withdrawal Requests')

@section('content')

{{-- Custom CSS mapping Tailwind to your Root Variables --}}
<style>
    /* Mapping Root Variables to Utility Classes */
    .text-b-blue { color: var(--brand-blue); }
    .bg-b-blue { background-color: var(--brand-blue); }
    .border-b-blue { border-color: var(--brand-blue); }

    .text-b-green { color: var(--brand-green); }
    .bg-b-green { background-color: var(--brand-green); }

    .text-b-pink { color: var(--brand-pink); }
    .bg-b-pink { background-color: var(--brand-pink); }

    .bg-b-sidebar { background-color: var(--sidebar-bg); }

    /* Compact Utilities */
    .table-compact th, .table-compact td {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    [x-cloak] { display: none !important; }
</style>

<div class="w-full px-4 py-6 mx-auto">

    {{-- 1. Compact Header & Filters --}}
    <div class="flex flex-col items-start justify-between gap-4 mb-6 md:flex-row md:items-center">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Payout Requests</h2>
            <p class="text-xs text-slate-500">Manage instructor withdrawals.</p>
        </div>

        {{-- Compact Filter Pills --}}
        <div class="inline-flex p-1 bg-white border rounded-lg shadow-sm border-slate-200">
            <a href="{{ route('admin.referrals.withdrawals') }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all
               {{ !request('status') ? 'bg-b-sidebar text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                All
            </a>
            <a href="{{ route('admin.referrals.withdrawals', ['status' => 'pending']) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all
               {{ request('status') == 'pending' ? 'bg-yellow-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                Pending
            </a>
            <a href="{{ route('admin.referrals.withdrawals', ['status' => 'approved']) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all
               {{ request('status') == 'approved' ? 'bg-b-green text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                Paid
            </a>
            <a href="{{ route('admin.referrals.withdrawals', ['status' => 'rejected']) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all
               {{ request('status') == 'rejected' ? 'bg-red-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                Rejected
            </a>
        </div>
    </div>

    {{-- 2. Flash Messages (Compact) --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between p-3 mb-4 text-xs font-medium text-green-700 bg-green-100 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            <button @click="show = false" class="opacity-50 hover:opacity-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between p-3 mb-4 text-xs font-medium text-red-700 bg-red-100 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            <button @click="show = false" class="opacity-50 hover:opacity-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    @endif

    {{-- 3. Compact Data Table --}}
    <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left table-compact">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                    <tr>
                        <th class="px-4">Ref ID</th>
                        <th class="px-4">Instructor</th>
                        <th class="px-4">Amount</th>
                        <th class="px-4">Payment Method</th>
                        <th class="px-4">Status</th>
                        <th class="px-4">Requested On</th>
                        <th class="px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13px] text-slate-600">
                    @forelse($withdrawals as $req)
                    <tr class="transition-colors hover:bg-slate-50" x-data="{ showApprove: false, showReject: false }">

                        {{-- ID --}}
                        <td class="px-4 font-mono text-xs font-semibold text-b-blue">
                            #{{ $req->id }}
                        </td>

                        {{-- Instructor --}}
                        <td class="px-4">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full bg-b-sidebar">
                                    {{ strtoupper(substr($req->user->full_name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="leading-tight">
                                    <div class="font-bold text-slate-800">{{ $req->user->full_name ?? 'Unknown' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $req->user->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Amount --}}
                        <td class="px-4">
                            <span class="font-bold text-slate-800">₹{{ number_format($req->amount, 0) }}</span>
                        </td>

                        {{-- Payment Details (Compact) --}}
                        <td class="px-4">
                            <div class="flex flex-col items-start gap-1">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border border-slate-200 bg-white text-slate-700">
                                    {{ $req->payment_method }}
                                </span>

                                {{-- Tooltip-style Details --}}
                                @php $details = json_decode($req->payment_details, true); @endphp
                                <div class="text-[10px] text-slate-500 leading-tight">
                                    @if(is_array($details))
                                        @if($req->payment_method == 'UPI')
                                            <span class="font-mono text-slate-700">{{ $details['upi_id'] ?? 'N/A' }}</span>
                                        @else
                                            <div class="flex flex-col">
                                                <span>{{ $details['bank_name'] ?? '' }}</span>
                                                <span class="font-mono text-slate-700">{{ $details['account_number'] ?? '' }}</span>
                                            </div>
                                        @endif
                                    @else
                                        ...
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-4">
                            @if($req->status == 'pending')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Pending
                                </span>
                            @elseif($req->status == 'approved')
                                <div class="flex flex-col items-start">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-b-green border border-green-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-b-green"></span> Paid
                                    </span>
                                    <span class="text-[9px] text-slate-400 mt-0.5 font-mono">Ref: {{ $req->transaction_id ?? '-' }}</span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                                </span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="px-4 text-[11px] text-slate-500">
                            {{ $req->created_at->format('d M, Y') }}<br>
                            <span class="text-slate-400">{{ $req->created_at->format('h:i A') }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 text-right">
                            @if($req->status == 'pending')
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="showApprove = true" class="p-1.5 text-white transition-colors rounded-md bg-b-green hover:opacity-90 shadow-sm shadow-green-200" title="Approve">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <button @click="showReject = true" class="p-1.5 text-white transition-colors rounded-md bg-red-500 hover:bg-red-600 shadow-sm shadow-red-200" title="Reject">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                {{-- ✅ APPROVE MODAL (Compact) --}}
                                <div x-cloak x-show="showApprove" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[2px]" x-transition.opacity>
                                    <div class="w-full max-w-sm mx-4 overflow-hidden bg-white shadow-xl rounded-xl" @click.away="showApprove = false">
                                        <div class="px-4 py-3 text-white bg-b-green">
                                            <h3 class="text-sm font-bold">Confirm Payout</h3>
                                        </div>
                                        <form action="{{ route('admin.referrals.approve', $req->id) }}" method="POST" class="p-4">
                                            @csrf
                                            <div class="p-2 mb-3 text-center border border-green-100 rounded-lg bg-green-50">
                                                <div class="text-[10px] text-green-600 uppercase font-bold">Paying Amount</div>
                                                <div class="text-xl font-extrabold text-b-green">₹{{ number_format($req->amount, 2) }}</div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="block mb-1 text-xs font-bold text-slate-700">Transaction Ref No <span class="text-red-500">*</span></label>
                                                <input type="text" name="transaction_id" required class="w-full px-3 py-2 text-xs font-semibold border rounded-lg border-slate-300 focus:ring-b-green focus:border-b-green" placeholder="e.g. UPI Ref ID">
                                            </div>

                                            <div class="mb-4">
                                                <label class="block mb-1 text-xs font-bold text-slate-700">Admin Note</label>
                                                <input type="text" name="admin_note" class="w-full px-3 py-2 text-xs border rounded-lg border-slate-300 focus:ring-b-green focus:border-b-green" placeholder="Optional">
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="showApprove = false" class="px-3 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 rounded-md hover:bg-slate-200">Cancel</button>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white rounded-md bg-b-green hover:opacity-90">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- ❌ REJECT MODAL (Compact) --}}
                                <div x-cloak x-show="showReject" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[2px]" x-transition.opacity>
                                    <div class="w-full max-w-sm mx-4 overflow-hidden bg-white shadow-xl rounded-xl" @click.away="showReject = false">
                                        <div class="px-4 py-3 text-white bg-red-600">
                                            <h3 class="text-sm font-bold">Reject Request</h3>
                                        </div>
                                        <form action="{{ route('admin.referrals.reject', $req->id) }}" method="POST" class="p-4">
                                            @csrf
                                            <p class="mb-3 text-xs leading-relaxed text-slate-600">
                                                Refund <strong class="text-slate-900">₹{{ number_format($req->amount, 2) }}</strong> to <strong class="text-slate-900">{{ $req->user->full_name }}</strong>?
                                            </p>

                                            <div class="mb-4">
                                                <label class="block mb-1 text-xs font-bold text-slate-700">Reason <span class="text-red-500">*</span></label>
                                                <textarea name="admin_note" rows="2" required class="w-full px-3 py-2 text-xs border rounded-lg border-slate-300 focus:ring-red-500 focus:border-red-500" placeholder="Why are you rejecting this?"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="showReject = false" class="px-3 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 rounded-md hover:bg-slate-200">Cancel</button>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-red-600 rounded-md hover:bg-red-700">Reject & Refund</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            @else
                                <span class="text-xs text-slate-300">
                                    <svg class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <p class="text-xs">No withdrawal requests found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Compact Pagination --}}
        @if($withdrawals->hasPages())
        <div class="px-4 py-3 border-t bg-slate-50 border-slate-100">
            {{ $withdrawals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
