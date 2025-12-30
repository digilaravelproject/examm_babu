@extends('layouts.student')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-8 sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold text-slate-900">Payment History</h1>
            <p class="mt-1 text-sm text-slate-500">View your transaction history and download invoices.</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex flex-col">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">

                @if($payments->count() > 0)
                    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-black ring-opacity-5 md:rounded-2xl">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500 sm:pl-6">Transaction ID</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Plan</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Amount</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Invoice</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($payments as $payment)
                                    <tr class="transition-colors group hover:bg-slate-50">
                                        {{-- Transaction ID --}}
                                        <td class="py-4 pl-4 pr-3 font-mono text-sm whitespace-nowrap text-slate-500 sm:pl-6">
                                            #{{ substr($payment->payment_id, -8) }}
                                        </td>

                                        {{-- Plan --}}
                                        <td class="px-3 py-4 text-sm font-bold whitespace-nowrap text-slate-900">
                                            {{ $payment->plan->name ?? 'Unknown Plan' }}
                                        </td>

                                        {{-- Amount --}}
                                        <td class="px-3 py-4 text-sm font-medium whitespace-nowrap text-slate-900">
                                            {{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}
                                        </td>

                                        {{-- Date --}}
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-slate-500">
                                            {{ $payment->created_at->format('M d, Y') }}
                                            <span class="block text-xs text-slate-400">{{ $payment->created_at->format('h:i A') }}</span>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if($payment->status === 'success')
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">
                                                    Success
                                                </span>
                                            @elseif($payment->status === 'pending')
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-bold text-yellow-800">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800">
                                                    Failed
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Action (Invoice) --}}
                                        <td class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">
                                            @if($enable_invoice && $payment->status === 'success')
                                                <a href="{{ route('student.payments.invoice', $payment->payment_id) }}"
                                                   class="text-[var(--brand-blue)] hover:text-blue-800 inline-flex items-center gap-1 transition-colors"
                                                   title="Download Invoice">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Invoice
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1 cursor-not-allowed text-slate-300">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                    N/A
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $payments->links() }}
                    </div>

                @else
                    {{-- Empty State --}}
                    <div class="py-20 text-center bg-white border border-dashed rounded-2xl border-slate-300">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full text-slate-300 bg-slate-50">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No payments found</h3>
                        <p class="mt-1 text-sm text-slate-500">You haven't made any transactions yet.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
