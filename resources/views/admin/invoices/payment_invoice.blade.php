<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $payment->invoice_id ?? $payment->payment_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; }

        .invoice-wrap { max-width: 800px; margin: 0 auto; padding: 30px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #0777be; padding-bottom: 20px; margin-bottom: 25px; }
        .header-left { width: 55%; }
        .header-right { width: 40%; text-align: right; }
        .company-name { font-size: 22px; font-weight: 700; color: #0777be; margin-bottom: 4px; }
        .company-detail { font-size: 11px; color: #64748b; line-height: 1.6; }
        .invoice-title { font-size: 28px; font-weight: 700; color: #0777be; letter-spacing: 2px; margin-bottom: 8px; }
        .invoice-meta { font-size: 11px; color: #64748b; }
        .invoice-meta strong { color: #334155; }

        /* Info Blocks */
        .info-row { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .info-block { width: 48%; }
        .info-block-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .info-block p { font-size: 11px; color: #475569; margin-bottom: 2px; }
        .info-block p strong { color: #1e293b; }

        /* Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table thead th { background: #0777be; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-table thead th:last-child { text-align: right; }
        .items-table tbody td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 12px; color: #334155; }
        .items-table tbody td:last-child { text-align: right; font-weight: 600; }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }

        /* Totals */
        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 25px; }
        .totals-table { width: 280px; }
        .totals-table tr td { padding: 6px 12px; font-size: 12px; color: #475569; }
        .totals-table tr td:last-child { text-align: right; font-weight: 600; color: #1e293b; }
        .totals-table .total-row td { border-top: 2px solid #0777be; font-size: 14px; font-weight: 700; color: #0777be; padding-top: 10px; }

        /* Footer */
        .footer { border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 30px; }
        .footer-note { font-size: 10px; color: #94a3b8; text-align: center; line-height: 1.6; }

        /* Status Badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-failed { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="invoice-wrap">

        {{-- ═══════════════════════════════════════════ HEADER ═══════════════════════════════════════════ --}}
        <table style="width:100%; margin-bottom: 25px; border-bottom: 3px solid #0777be; padding-bottom: 15px;">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-bottom: 15px;">
                    <div class="company-name">{{ $vendor['company_name'] ?? 'Exam Babu' }}</div>
                    <div class="company-detail">
                        @if(!empty($vendor['address']))
                            {{ $vendor['address'] }}<br>
                        @endif
                        @if(!empty($vendor['city']) || !empty($vendor['state']) || !empty($vendor['zip_code']))
                            {{ $vendor['city'] ?? '' }}{{ !empty($vendor['state']) ? ', '.$vendor['state'] : '' }} {{ $vendor['zip_code'] ?? '' }}<br>
                        @endif
                        @if(!empty($vendor['country']))
                            {{ $vendor['country'] }}<br>
                        @endif
                        @if(!empty($vendor['phone']))
                            Phone: {{ $vendor['phone'] }}<br>
                        @endif
                        @if(!empty($vendor['email']))
                            Email: {{ $vendor['email'] }}<br>
                        @endif
                        @if(!empty($vendor['gstin']))
                            <strong>GSTIN: {{ $vendor['gstin'] }}</strong>
                        @endif
                    </div>
                </td>
                <td style="width: 45%; vertical-align: top; text-align: right; padding-bottom: 15px;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">
                        <strong>Invoice #:</strong> {{ $payment->invoice_id ?? $payment->payment_id }}<br>
                        <strong>Date:</strong> {{ $date }}<br>
                        <strong>Status:</strong>
                        @if($payment->status === 'success')
                            <span class="badge badge-success">PAID</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge badge-pending">PENDING</span>
                        @else
                            <span class="badge badge-failed">{{ strtoupper($payment->status) }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- ═══════════════════════════════════════════ BILL TO / PAYMENT INFO ═══════════════════════════════════════════ --}}
        <table style="width: 100%; margin-bottom: 25px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="info-block">
                        <div class="info-block-label">Billed To</div>
                        <p><strong>{{ $payment->user->name ?? 'N/A' }}</strong></p>
                        <p>{{ $payment->user->email ?? '' }}</p>
                        @if(!empty($payment->user->phone))
                            <p>Phone: {{ $payment->user->phone }}</p>
                        @endif
                        @php $customerBilling = $payment->data['customer_billing_information'] ?? []; @endphp
                        @if(!empty($customerBilling['address']))
                            <p>{{ $customerBilling['address'] }}</p>
                        @endif
                        @if(!empty($customerBilling['city']) || !empty($customerBilling['state']))
                            <p>{{ $customerBilling['city'] ?? '' }}{{ !empty($customerBilling['state']) ? ', '.$customerBilling['state'] : '' }} {{ $customerBilling['zip_code'] ?? '' }}</p>
                        @endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="info-block" style="text-align: right;">
                        <div class="info-block-label" style="text-align: right;">Payment Details</div>
                        <p><strong>Method:</strong> {{ ucfirst($payment->payment_processor ?? 'N/A') }}</p>
                        @if($payment->transaction_id)
                            <p><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</p>
                        @endif
                        <p><strong>Currency:</strong> {{ strtoupper($payment->currency ?? 'INR') }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ═══════════════════════════════════════════ ITEMS TABLE ═══════════════════════════════════════════ --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th>Duration</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $payment->plan->name ?? 'Subscription Plan' }}</strong>
                        @if($payment->plan && $payment->plan->description)
                            <br><span style="font-size: 10px; color: #94a3b8;">{{ Str::limit($payment->plan->description, 80) }}</span>
                        @endif
                    </td>
                    <td>{{ ($payment->plan->duration ?? 1) }} Month(s)</td>
                    <td>1</td>
                    <td style="text-align: right;">
                        @php
                            $subtotal = $order_summary['subtotal'] ?? $payment->total_amount;
                            $currencySymbol = $order_summary['currency_symbol'] ?? '₹';
                        @endphp
                        {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- ═══════════════════════════════════════════ TOTALS ═══════════════════════════════════════════ --}}
        <table style="width: 100%; margin-bottom: 25px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%;">
                    <table style="width: 100%;" class="totals-table">
                        <tr>
                            <td>Subtotal</td>
                            <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</td>
                        </tr>

                        {{-- Tax Line Items --}}
                        @if(!empty($order_summary['taxes']))
                            @foreach($order_summary['taxes'] as $tax)
                                <tr>
                                    <td>{{ $tax['name'] ?? 'Tax' }} ({{ $tax['rate'] ?? '0' }}%)</td>
                                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($tax['amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @elseif(isset($tax_settings) && $tax_settings->enable_tax)
                            {{-- Fallback: Calculate from current settings if order_summary doesn't have taxes --}}
                            @php
                                $tax1Amount = $tax_settings->tax_amount_type === 'percentage'
                                    ? ($subtotal * $tax_settings->tax_amount / 100)
                                    : $tax_settings->tax_amount;
                            @endphp
                            <tr>
                                <td>{{ $tax_settings->tax_name }} ({{ $tax_settings->tax_amount }}%)</td>
                                <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($tax1Amount, 2) }}</td>
                            </tr>

                            @if($tax_settings->enable_additional_tax)
                                @php
                                    $tax2Amount = $tax_settings->additional_tax_amount_type === 'percentage'
                                        ? ($subtotal * $tax_settings->additional_tax_amount / 100)
                                        : $tax_settings->additional_tax_amount;
                                @endphp
                                <tr>
                                    <td>{{ $tax_settings->additional_tax_name }} ({{ $tax_settings->additional_tax_amount }}%)</td>
                                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($tax2Amount, 2) }}</td>
                                </tr>
                            @endif
                        @endif

                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td style="text-align: right;"><strong>{{ $currencySymbol }}{{ number_format($payment->total_amount, 2) }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- ═══════════════════════════════════════════ FOOTER ═══════════════════════════════════════════ --}}
        <div class="footer">
            <div class="footer-note">
                This is a computer-generated invoice and does not require a signature.<br>
                Thank you for your purchase! For queries, contact us at <strong>{{ $vendor['email'] ?? 'support@exambabu.com' }}</strong>
            </div>
        </div>
    </div>
</body>
</html>
