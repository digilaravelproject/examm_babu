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
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-xs font-bold text-[#0777be] uppercase mb-2">Billed To</h3>
                    <p class="text-lg font-bold text-gray-900">{{ $payment->user->name ?? $payment->user->first_name }}</p>

                    @if(isset($data['customer_billing_information']))
                         <p class="text-sm text-gray-600 leading-relaxed mt-1">
                            {{ $data['customer_billing_information']['address'] ?? '' }}<br>
                            {{ $data['customer_billing_information']['city'] ?? '' }}, {{ $data['customer_billing_information']['state'] ?? '' }} - {{ $data['customer_billing_information']['zip'] ?? '' }}<br>
                            {{ $data['customer_billing_information']['phone'] ?? '' }}
                        </p>
                    @else
                        <p class="text-sm text-gray-600 mt-1">{{ $payment->user->email }}</p>
                    @endif
                </div>
                <div class="text-right sm:text-right">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600"><span class="font-bold text-gray-800">Invoice ID:</span> #{{ $payment->invoice_id ?? str_pad((string)$payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p class="text-sm text-gray-600"><span class="font-bold text-gray-800">Transaction ID:</span> {{ $payment->transaction_id ?? $payment->payment_id }}</p>
                        <p class="text-sm text-gray-600"><span class="font-bold text-gray-800">Date:</span> {{ \Carbon\Carbon::parse($payment->created_at)->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="border border-gray-200 rounded-lg overflow-hidden mb-8">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-900 font-bold uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4 text-center">Duration</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $payment->plan->name ?? 'Subscription Plan' }}</p>
                                <p class="text-xs text-gray-500">Access to Exam Series</p>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $payment->plan->duration ?? 12 }} Months</td>
                            <td class="px-6 py-4 text-right font-medium">{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="flex justify-end mb-12">
                <div class="w-full sm:w-1/2 lg:w-1/3 space-y-3">
                    @if(isset($data['order_summary']['sub_total']))
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ $payment->currency }} {{ number_format($data['order_summary']['sub_total'], 2) }}</span>
                    </div>
                    @endif

                    @if(isset($data['order_summary']['discount_amount']) && $data['order_summary']['discount_amount'] > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Discount</span>
                        <span>- {{ $payment->currency }} {{ number_format($data['order_summary']['discount_amount'], 2) }}</span>
                    </div>
                    @endif

                    @if(isset($data['order_summary']['taxes']))
                        @foreach($data['order_summary']['taxes'] as $tax)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ $tax['name'] }}</span>
                            <span>+ {{ $payment->currency }} {{ number_format($tax['amount'], 2) }}</span>
                        </div>
                        @endforeach
                    @endif

                    <div class="flex justify-between text-base font-bold text-[#0777be] border-t pt-3 mt-3">
                        <span>Total Paid</span>
                        <span>{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="bg-gray-50 rounded-lg p-4 text-xs text-gray-500">
                <p class="font-bold mb-1">Terms & Conditions:</p>
                <p>This is a computer-generated invoice and does not require a physical signature. Payment is non-refundable. For any queries, please contact support.</p>
            </div>

        </div>
    </div>
</div>
@endsection
