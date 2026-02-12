@extends(auth()->user()->hasRole('student') ?'layouts.student' : 'layouts.instructor' )

@section('content')

@php
    $currentRole = request()->route('role');
@endphp

<style>
    /* Branding Variables */
    :root {
        --brand-blue: #0777be;
        --brand-pink: #f062a4;
        --brand-green: #94c940;
    }
    [x-cloak] { display: none !important; }
    .btn-brand-primary { background-color: var(--brand-blue); color: white; transition: 0.2s; }
    .btn-brand-primary:hover { background-color: #055c93; box-shadow: 0 4px 12px rgba(7, 119, 190, 0.3); }
    .btn-disabled { background-color: #cbd5e1; color: #64748b; cursor: not-allowed; }
</style>

<div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8 bg-slate-50" x-data="{ showWithdrawModal: false }">

    {{-- 1. Header & Wallet Overview --}}
    <div class="flex flex-col justify-between pb-6 mb-10 border-b md:flex-row md:items-center border-slate-200">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Refer & Earn</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">
                Invite friends and earn <span class="font-bold text-slate-800">{{ $commissionRate }}% commission</span> on every purchase.
            </p>
        </div>

        <div class="flex items-center gap-4 mt-4 md:mt-0">
            {{-- Balance Card --}}
            <div class="flex items-center gap-3 px-4 py-2 bg-white border shadow-sm rounded-xl border-slate-200">
                <div class="p-2 bg-green-100 rounded-full">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-500">Wallet Balance</p>
                    <p class="text-lg font-extrabold text-slate-800">₹{{ number_format($totalEarnings, 2) }}</p>
                </div>
            </div>

            {{-- Withdraw Button --}}
            <button
                @if($canWithdraw) @click="showWithdrawModal = true" @endif
                @class([
                    'px-5 py-3 rounded-xl font-bold text-sm shadow-sm flex items-center gap-2',
                    'btn-brand-primary active:scale-95' => $canWithdraw,
                    'btn-disabled' => !$canWithdraw
                ])
                title="{{ $canWithdraw ? 'Click to withdraw' : 'Min balance ₹'.number_format($minLimit).' required' }}"
                {{ $canWithdraw ? '' : 'disabled' }}
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Request Payout
            </button>
        </div>
    </div>

    {{-- 2. Statistics Grid --}}
    <div class="grid grid-cols-1 gap-6 mb-10 md:grid-cols-3">
        {{-- Total Referrals --}}
        <div class="p-6 bg-white border-l-4 shadow-sm rounded-xl border-brand-blue">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold tracking-wide uppercase text-slate-500">Total Referrals</div>
                    <div class="mt-2 text-3xl font-extrabold text-slate-800">{{ $totalReferrals }}</div>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 text-brand-blue">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>
        </div>

        {{-- Commission Rate --}}
        <div class="p-6 bg-white border-l-4 shadow-sm rounded-xl border-brand-pink">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold tracking-wide uppercase text-slate-500">Commission Rate</div>
                    <div class="mt-2 text-3xl font-extrabold text-slate-800">{{ $commissionRate }}%</div>
                </div>
                <div class="p-3 rounded-lg bg-pink-50 text-brand-pink">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
            </div>
        </div>

        {{-- Min Withdrawal --}}
        <div class="p-6 bg-white border-l-4 shadow-sm rounded-xl border-brand-green">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold tracking-wide uppercase text-slate-500">Min Withdrawal</div>
                    <div class="mt-2 text-3xl font-extrabold text-slate-800">₹{{ number_format($minLimit) }}</div>
                </div>
                <div class="p-3 rounded-lg bg-green-50 text-brand-green">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. General Referral Link --}}
    <div class="flex flex-col items-center justify-between gap-8 p-8 mb-10 text-center bg-white border shadow-sm rounded-xl border-slate-200 md:text-left md:flex-row">
        <div class="max-w-xl">
            <h3 class="mb-2 text-xl font-bold text-slate-900">Share your general link</h3>
            <p class="text-sm leading-relaxed text-slate-500">Copy this link to direct students to the home page. You will earn commission on any plan they purchase.</p>
        </div>

        <div class="flex-1 w-full max-w-lg md:w-auto" x-data="{ copied: false }">
            <div class="relative flex items-center">
                <input type="text" value="{{ $referralLink }}" readonly class="block w-full py-3 pl-4 pr-32 font-mono text-sm border rounded-lg bg-slate-50 border-slate-300 text-slate-600 focus:ring-brand-blue focus:border-brand-blue">
                <button @click="navigator.clipboard.writeText('{{ $referralLink }}'); copied = true; setTimeout(() => copied = false, 2000);"
                        class="absolute flex items-center justify-center px-5 text-sm font-bold rounded-md right-1 top-1 bottom-1 btn-brand-primary">
                    <span x-show="!copied" class="flex items-center gap-2">Copy</span>
                    <span x-show="copied" x-cloak class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Copied!</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 4. Promote Specific Plans --}}
    @if($plans->count() > 0)
    <div class="mb-10">
        <h3 class="mb-4 text-xl font-bold text-slate-900">Promote Specific Plans</h3>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    // Note: Direct Checkout links are public, they don't need role prefix
                    $planLink = url('/checkout/' . $plan->code) . '?ref=' . (Auth::user()->referral_code ?? 'CODE');
                @endphp
                <div class="relative flex flex-col justify-between h-full p-5 overflow-hidden transition-shadow bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-md">
                    <div class="absolute top-0 left-0 w-full h-1 bg-brand-blue"></div>
                    <div class="mb-4">
                        <div class="flex items-start justify-between mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-brand-blue bg-blue-50 px-2 py-1 rounded border border-blue-100">
                                {{ $plan->category_type ?? 'Plan' }}
                            </span>
                            <div class="text-lg font-extrabold text-slate-800">₹{{ $plan->price }}</div>
                        </div>
                        <h4 class="text-lg font-bold leading-tight text-slate-900 line-clamp-2">{{ $plan->name }}</h4>
                        <p class="mt-2 text-xs text-slate-500 line-clamp-2">{{ $plan->description ?? 'Comprehensive coverage for exams.' }}</p>
                    </div>

                    <div x-data="{ copied: false }" class="pt-4 mt-auto border-t border-slate-100">
                        <button @click="navigator.clipboard.writeText('{{ $planLink }}'); copied = true; setTimeout(() => copied = false, 2000);"
                                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-bold text-sm hover:border-brand-blue hover:text-brand-blue transition-all bg-white hover:bg-blue-50">
                            <span x-show="!copied">Copy Checkout Link</span>
                            <span x-show="copied" x-cloak class="flex items-center gap-2 text-green-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Copied!</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 5. Wallet Passbook (History) --}}
    <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-xl">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-900">Wallet Passbook</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs uppercase border-b text-slate-700 bg-slate-50 border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-bold">Date</th>
                        <th class="px-6 py-4 font-bold">Description</th>
                        <th class="px-6 py-4 font-bold">Type</th>
                        <th class="px-6 py-4 font-bold">Amount</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse(Auth::user()->walletTransactions()->with('withdrawalRequest')->latest()->limit(50)->get() as $txn)
                    <tr class="transition-colors hover:bg-slate-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $txn->created_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-900">{{ $txn->description }}</span>
                            @if($txn->source == 'withdrawal' && $txn->reference_id)
                                <div class="text-xs text-slate-400 mt-0.5">Ref ID: #{{ $txn->reference_id }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span @class([
                                'px-2 py-1 rounded text-[10px] font-extrabold uppercase tracking-wide',
                                'text-green-700 bg-green-100' => $txn->type == 'credit',
                                'text-red-700 bg-red-100' => $txn->type != 'credit'
                            ])>
                                {{ $txn->type == 'credit' ? 'INCOME' : 'WITHDRAWAL' }}
                            </span>
                        </td>
                        <td @class(['px-6 py-4 font-bold whitespace-nowrap', 'text-green-600' => $txn->type == 'credit', 'text-red-600' => $txn->type != 'credit'])>
                            {{ $txn->type == 'credit' ? '+' : '' }} ₹{{ number_format(abs($txn->amount), 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($txn->source == 'withdrawal')
                                @php $req = $txn->withdrawalRequest; @endphp
                                @if($req)
                                    @switch($req->status)
                                        @case('pending')
                                            <span class="inline-flex items-center gap-1 text-yellow-700 bg-yellow-100 px-2 py-1 rounded text-[10px] font-bold uppercase"><span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="inline-flex items-center gap-1 text-green-700 bg-green-100 px-2 py-1 rounded text-[10px] font-bold uppercase"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Paid</span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center gap-1 text-red-700 bg-red-100 px-2 py-1 rounded text-[10px] font-bold uppercase"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected</span>
                                    @endswitch
                                @else
                                    <span class="text-xs text-slate-400">N/A</span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 text-green-700 bg-green-100 px-2 py-1 rounded text-[10px] font-bold uppercase"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Success</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            <p>No transactions found yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. Withdrawal Modal --}}
    <div x-cloak x-show="showWithdrawModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         x-transition.opacity>

        <div class="w-full max-w-lg mx-4 bg-white shadow-2xl rounded-2xl"
             @click.away="showWithdrawModal = false"
             x-data="{ paymentMethod: 'UPI' }">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-lg font-extrabold text-slate-800">Request Payout</h3>
                <button @click="showWithdrawModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{--
                FIX: Form Action Updated
                Action ab dynamic 'panel.referral.withdraw.store' route use kar raha hai
                aur sath mein {role} parameter pass kar raha hai.
            --}}
            <form action="{{ route('panel.referral.withdraw.store', ['role' => $currentRole]) }}" method="POST" class="p-6 space-y-5">
                @csrf
                {{-- Amount --}}
                <div>
                    <label class="block mb-1.5 text-sm font-bold text-slate-700">Amount to Withdraw</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-500 font-bold">₹</span>
                        <input type="number" step="0.01" name="amount" value="{{ $totalEarnings }}" min="{{ $minLimit }}" max="{{ $totalEarnings }}"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-brand-blue font-bold text-slate-900" required>
                    </div>
                    <div class="flex justify-between mt-1 text-xs">
                        <span class="text-slate-500">Available: <span class="font-bold">₹{{ number_format($totalEarnings, 2) }}</span></span>
                        <span class="text-slate-400">Min: ₹{{ number_format($minLimit) }}</span>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div>
                    <label class="block mb-1.5 text-sm font-bold text-slate-700">Payment Method</label>
                    <select name="payment_method" x-model="paymentMethod" class="w-full border-slate-300 rounded-lg focus:ring-brand-blue py-2.5 px-3 text-slate-700 font-medium">
                        <option value="UPI">UPI (GPay / PhonePe / Paytm)</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>

                {{-- Dynamic Fields --}}
                <div x-show="paymentMethod === 'UPI'" x-transition>
                    <label class="block mb-1.5 text-sm font-bold text-slate-700">UPI ID</label>
                    <input type="text" name="upi_id" placeholder="e.g. 9876543210@upi" class="w-full px-3 py-2.5 border-slate-300 rounded-lg focus:ring-brand-blue">
                </div>

                <div x-show="paymentMethod === 'Bank Transfer'" x-cloak class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-700">Bank Name</label>
                            <input type="text" name="bank_name" placeholder="e.g. SBI" class="w-full px-3 py-2 text-sm rounded-lg border-slate-300">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-700">IFSC Code</label>
                            <input type="text" name="ifsc_code" placeholder="SBIN000xxxx" class="w-full px-3 py-2 text-sm uppercase rounded-lg border-slate-300">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Account Holder Name</label>
                        <input type="text" name="account_holder_name" class="w-full px-3 py-2 text-sm rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Account Number</label>
                        <input type="password" name="account_number" class="w-full px-3 py-2 text-sm rounded-lg border-slate-300">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 btn-brand-primary font-bold rounded-xl shadow-lg active:scale-[0.98]">
                    Submit Withdrawal Request
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
