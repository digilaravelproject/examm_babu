@extends('layouts.student') {{-- Aapka main layout --}}

@section('title', 'Invoice Preview')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Action Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoice Preview</h1>
            <p class="text-sm text-gray-500">Invoice #{{ $payment->invoice_id ?? $payment->id }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('student.payments.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg shadow-sm hover:bg-gray-50 font-medium transition-colors">
                Back to List
            </a>

            {{-- DOWNLOAD BUTTON --}}
            <a href="{{ route('student.payments.invoice.download', $payment->payment_id ?? $payment->id) }}"
               class="flex items-center gap-2 px-4 py-2 bg-[#0777be] text-white rounded-lg shadow-md hover:bg-[#0666a3] font-bold transition-transform transform active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    {{-- Invoice Paper UI --}}
    <div class="bg-white shadow-xl rounded-none sm:rounded-lg border border-gray-200 overflow-hidden print:shadow-none print:border-none">

        {{-- Iframe Approach (Optional: Agar exact PDF dikhana hai) --}}
        {{-- <iframe src="..." class="w-full h-screen"></iframe> --}}

        {{-- HTML Representation (Visual Replica of PDF) --}}
        <div class="p-8 sm:p-12 min-h-[800px] relative">

            {{-- Header --}}
            <div class="flex justify-between items-start border-b border-gray-100 pb-8 mb-8">
                <div>
                    @if($logo)
                        <img src="{{ $logo }}" class="h-12 w-auto mb-2" alt="Logo">
                    @else
                        <h1 class="text-3xl font-bold text-[#0777be]">{{ $siteSettings->app_name ?? 'Exam Babu' }}</h1>
                    @endif
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wide">Invoice</h2>
                    <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-700 text-xs font-bold uppercase rounded-full">
                        Paid
                    </span>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-12 mb-10 pb-10 border-b border-gray-100">
                {{-- Billed From --}}
                <div class="relative pl-6 border-l-4 border-[#0777be]">
                    <h3 class="text-xs font-black text-[#0777be] uppercase tracking-widest mb-3">Issued By</h3>
                    <div class="space-y-1">
                        <p class="text-xl font-black text-gray-900 leading-tight">{{ $billingSettings->vendor_name ?? $siteSettings->app_name }}</p>
                        <p class="text-sm text-gray-600 leading-relaxed font-medium">
                            {{ $billingSettings->address }}<br>
                            {{ $billingSettings->city }}, {{ $billingSettings->state }} - {{ $billingSettings->zip }}<br>
                            {{ $billingSettings->country }}
                        </p>
                        <div class="pt-2 space-y-1">
                            @if(!empty($billingSettings->phone_number))
                                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $billingSettings->phone_number }}
                                </p>
                            @endif
                            @if(!empty($billingSettings->vat_number))
                                <p class="text-xs text-gray-500 font-bold tracking-tight bg-gray-100 px-2 py-0.5 rounded inline-block">
                                    GST/VAT: {{ $billingSettings->vat_number }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Billed To --}}
                <div class="text-right sm:text-right flex flex-col items-end">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Billed To</h3>
                    <p class="text-xl font-black text-gray-900 leading-tight mb-2">{{ $payment->user->name ?? $payment->user->first_name }}</p>

                    @if(isset($data['customer_billing_information']))
                         <p class="text-sm text-gray-600 leading-relaxed font-medium">
                            {{ $data['customer_billing_information']['address'] ?? '' }}<br>
                            {{ $data['customer_billing_information']['city'] ?? '' }}, {{ $data['customer_billing_information']['state'] ?? '' }} - {{ $data['customer_billing_information']['zip'] ?? '' }}<br>
                            {{ $data['customer_billing_information']['phone'] ?? '' }}
                        </p>
                    @else
                        <p class="text-sm text-gray-600 font-medium">{{ $payment->user->email }}</p>
                    @endif
                </div>
            </div>

            {{-- Meta Bar --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 bg-gray-50 rounded-xl p-6 border border-gray-100">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Invoice Number</p>
                    <p class="text-sm font-bold text-gray-900">#{{ $payment->invoice_id ?? str_pad((string)$payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Issue Date</p>
                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($payment->created_at)->format('d M, Y') }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Transaction ID</p>
                    <p class="text-sm font-bold text-gray-900 truncate pr-4" title="{{ $payment->transaction_id ?? $payment->payment_id }}">
                        {{ substr($payment->transaction_id ?? $payment->payment_id, 0, 12) }}...
                    </p>
                </div>
                <div class="space-y-1 text-right sm:text-left">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Payment Status</p>
                    <p class="text-sm font-black text-green-600 uppercase">Paid</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden mb-10 shadow-sm">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-900 font-bold uppercase text-[10px] tracking-widest border-b border-gray-200">
                        <tr>
                            <th class="px-8 py-5">Description</th>
                            <th class="px-8 py-5 text-center">Duration</th>
                            <th class="px-8 py-5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-6">
                                <p class="font-black text-gray-900 text-base mb-1">{{ $payment->plan->name ?? 'Subscription Plan' }}</p>
                                <p class="text-xs text-gray-500 font-medium">Access to Premium Exam Series & Resources</p>
                            </td>
                            <td class="px-8 py-6 text-center font-bold text-gray-700">{{ $payment->plan->duration ?? 12 }} Months</td>
                            <td class="px-8 py-6 text-right font-black text-gray-900 text-base tracking-tight">
                                {{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="flex justify-end mb-16 px-4">
                <div class="w-full sm:w-1/2 lg:w-1/3 border-2 border-[#0777be] rounded-2xl p-6 bg-[#0777be]/[0.02] shadow-sm">
                    @if(isset($data['order_summary']['sub_total']))
                    <div class="flex justify-between text-sm text-gray-600 font-bold mb-3">
                        <span>Subtotal</span>
                        <span class="text-gray-900">{{ $payment->currency }} {{ number_format($data['order_summary']['sub_total'], 2) }}</span>
                    </div>
                    @endif

                    @if(isset($data['order_summary']['discount_amount']) && $data['order_summary']['discount_amount'] > 0)
                    <div class="flex justify-between text-sm text-green-600 font-bold mb-3">
                        <span>Discount</span>
                        <span>- {{ $payment->currency }} {{ number_format($data['order_summary']['discount_amount'], 2) }}</span>
                    </div>
                    @endif

                    @if(isset($data['order_summary']['taxes']))
                        @foreach($data['order_summary']['taxes'] as $tax)
                        <div class="flex justify-between text-sm text-gray-600 font-bold mb-3">
                            <span>{{ $tax['name'] }}</span>
                            <span class="text-gray-900">+ {{ $payment->currency }} {{ number_format($tax['amount'], 2) }}</span>
                        </div>
                        @endforeach
                    @endif

                    <div class="flex justify-between items-center text-xl font-black text-[#0777be] border-t-2 border-[#0777be]/10 pt-4 mt-2">
                        <span>Total Amount</span>
                        <span>{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Signature/Footer --}}
            <div class="flex flex-col sm:flex-row justify-between items-end gap-10">
                <div class="max-w-md">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 italic">
                        <p class="text-[10px] font-black text-gray-400 uppercase mb-2 not-italic tracking-wider">Note to Customer:</p>
                        <p class="text-xs text-gray-500 leading-relaxed font-medium">Thank you for your trust. This is a computer-generated invoice and does not require a physical signature. For any queries, please reach out to our billing desk.</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="w-48 h-12 border-b-2 border-gray-200 mb-2"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest uppercase">Authorized Signatory</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
