<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        {{-- Table Head --}}
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-xs font-bold text-left uppercase text-slate-500 tracking-wider">ID</th>
                <th class="px-6 py-3 text-xs font-bold text-left uppercase text-slate-500 tracking-wider">User</th>
                <th class="px-6 py-3 text-xs font-bold text-left uppercase text-slate-500 tracking-wider">Plan / Amount</th>
                <!--<th class="px-6 py-3 text-xs font-bold text-left uppercase text-slate-500 tracking-wider">Method</th>-->
                <th class="px-6 py-3 text-xs font-bold text-left uppercase text-slate-500 tracking-wider">Status</th>
                <th class="px-6 py-3 text-xs font-bold text-right uppercase text-slate-500 tracking-wider">Actions</th>
            </tr>
        </thead>

        {{-- Table Body --}}
        <tbody class="bg-white divide-y divide-slate-200">
            @forelse($payments as $payment)
                <tr class="transition-colors hover:bg-slate-50">

                    {{-- ID Column --}}
                    <td class="px-6 py-4 whitespace-nowrap group">
                        <div class="flex items-center space-x-2">
                            <span class="font-mono text-sm font-medium text-slate-600">{{ $payment->payment_id }}</span>
                            <button onclick="navigator.clipboard.writeText('{{ $payment->payment_id }}');" class="opacity-0 transition-opacity text-slate-300 hover:text-blue-600 group-hover:opacity-100" title="Copy ID">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-8a2 2 0 012-2z"></path></svg>
                            </button>
                        </div>
                    </td>

                    {{-- User Column --}}
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-slate-800">
                            {{ $payment->user?->first_name ?? 'Unknown User' }} {{ $payment->user?->last_name ?? '' }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $payment->user?->email ?? 'No Email' }}
                        </div>
                    </td>

                    {{-- Plan / Amount --}}
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-800">
                            {{ $payment->plan?->name ?? 'Unknown Plan' }}
                        </div>
                        <div class="font-mono text-xs text-slate-500">
                            {{ $payment->currency ?? 'INR' }} {{ number_format($payment->amount, 2) }}
                        </div>
                    </td>

                    {{-- Method --}}
                    <!--<td class="px-6 py-4 text-sm font-bold tracking-wide uppercase text-slate-600">-->
                    <!--    {{ $payment->method }}-->
                    <!--</td>-->

                    {{-- Status Badge --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $status = strtolower($payment->status);
                            $color = 'bg-slate-100 text-slate-700 border border-slate-200';
                            if ($status == 'success' || $status == 'approved') $color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                            if ($status == 'failed' || $status == 'rejected') $color = 'bg-red-50 text-red-600 border border-red-100';
                            if ($status == 'pending') $color = 'bg-amber-50 text-amber-700 border border-amber-100';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase {{ $color }}">
                            {{ $status }}
                        </span>
                    </td>

                    {{-- Actions (SVG Icons) --}}
                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            
                            @if($payment->status == 'pending')
                                {{-- Approve Button --}}
                                <button onclick="paymentManager().approvePayment({{ $payment->id }}, 'approve')" 
                                    class="p-2 transition-colors rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700" 
                                    title="Approve & Activate">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>

                                {{-- Reject Button --}}
                                <button onclick="paymentManager().approvePayment({{ $payment->id }}, 'reject')" 
                                    class="p-2 transition-colors rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700" 
                                    title="Reject Payment">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            @endif

                            {{-- View Details (Eye Icon) --}}
                            <button @click="$dispatch('open-drawer', { id: {{ $payment->id }} })" 
                                class="p-2 transition-colors rounded-lg text-slate-400 hover:text-[#0f172a] hover:bg-slate-100" 
                                title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>

                            {{-- Delete (Trash Icon) --}}
                            <button onclick="paymentManager().deletePayment({{ $payment->id }})" 
                                class="p-2 transition-colors rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50" 
                                title="Delete Record">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>No payments found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (isset($payments) && $payments->hasPages())
    <div class="px-6 py-4 bg-white border-t border-slate-200">
        {{ $payments->appends(request()->query())->links() }}
    </div>
@endif