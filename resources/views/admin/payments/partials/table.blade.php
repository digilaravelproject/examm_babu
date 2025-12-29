<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        {{-- Table Head --}}
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">USER</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">PLAN / AMOUNT</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">METHOD</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">STATUS</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">ACTIONS</th>
            </tr>
        </thead>

        {{-- Table Body --}}
        <tbody class="bg-white divide-y divide-slate-200">
            @forelse($payments as $payment)
                <tr class="hover:bg-slate-50 transition-colors">

                    {{-- ID Column --}}
                    <td class="px-6 py-4 whitespace-nowrap group">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-mono font-medium text-slate-600">{{ $payment->payment_id }}</span>
                            <button onclick="navigator.clipboard.writeText('{{ $payment->payment_id }}'); alert('Copied ID')" class="text-slate-300 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-8a2 2 0 012-2z"></path></svg>
                            </button>
                        </div>
                    </td>

                    {{-- User Column (Safe Access) --}}
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-slate-800">
                            {{-- ✅ FIX: Added ?-> to prevent 500 Error if user is deleted --}}
                            {{ $payment->user?->first_name ?? 'Unknown User' }} {{ $payment->user?->last_name ?? '' }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $payment->user?->email ?? 'No Email' }}
                        </div>
                    </td>

                    {{-- Plan / Amount (Safe Access) --}}
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-800">
                            {{-- ✅ FIX: Added ?-> here too --}}
                            {{ $payment->plan?->name ?? 'Unknown Plan' }}
                        </div>
                        <div class="text-xs text-slate-500 font-mono">
                            {{ $payment->currency ?? 'INR' }} {{ $payment->amount }}
                        </div>
                    </td>

                    {{-- Method --}}
                    <td class="px-6 py-4 text-sm text-slate-600 uppercase font-bold tracking-wide">
                        {{ $payment->method }}
                    </td>

                    {{-- Status Badge --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $status = strtolower($payment->status);
                            $color = 'bg-slate-100 text-slate-700 border border-slate-200';
                            if ($status == 'success') $color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                            if ($status == 'failed') $color = 'bg-red-50 text-red-600 border border-red-100';
                            if ($status == 'pending') $color = 'bg-amber-50 text-amber-700 border border-amber-100';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full uppercase {{ $color }}">
                            {{ $status }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">

                        {{-- Approve/Reject Buttons --}}
                        @if($payment->status == 'pending')
                            <div class="flex justify-end items-center space-x-2 mb-2">
                                <button onclick="paymentManager().approvePayment({{ $payment->id }}, 'approve')"
                                    class="text-xs bg-emerald-600 text-white px-2 py-1 rounded hover:bg-emerald-700 transition-colors shadow-sm">
                                    Authorize
                                </button>
                                <button onclick="paymentManager().approvePayment({{ $payment->id }}, 'reject')"
                                    class="text-xs bg-white border border-red-200 text-red-600 px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                    Reject
                                </button>
                            </div>
                        @endif

                        {{-- Details Dropdown --}}
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button @click="open = !open" type="button"
                                class="bg-white rounded-md p-1.5 inline-flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none ring-1 ring-slate-200 transition-all">
                                <span class="sr-only">Open options</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" style="display: none;"
                                class="origin-top-right absolute right-0 mt-2 w-36 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-slate-100">
                                <div class="py-1">
                                    <button @click="$dispatch('open-drawer', { id: {{ $payment->id }} }); open = false"
                                        class="group flex items-center w-full px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#0f172a] transition-colors">
                                        <svg class="mr-3 h-4 w-4 text-slate-400 group-hover:text-[#0f172a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Details
                                    </button>
                                </div>
                                <div class="py-1">
                                    <button onclick="paymentManager().deletePayment({{ $payment->id }});"
                                        class="group flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="mr-3 h-4 w-4 text-red-400 group-hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>No payments found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (isset($payments) && $payments->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 bg-white">
        {{ $payments->appends(request()->query())->links() }}
    </div>
@endif
