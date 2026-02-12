<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $payment->invoice_id ?? $payment->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 0 auto;
            padding: 40px;
            max-width: 800px; /* Optional constraint */
        }

        /* --- Colors --- */
        .text-brand { color: #0777be; }
        .bg-brand { background-color: #0777be; color: white; }
        .text-gray { color: #555; }
        .text-light { color: #777; }

        /* --- Typography --- */
        h1, h2, h3, h4, h5 { margin: 0; font-weight: bold; }
        .font-bold { font-weight: bold; }
        .text-sm { font-size: 12px; }
        .text-xs { font-size: 10px; }
        .uppercase { text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* --- Header Section --- */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0777be;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo { height: 50px; }
        .invoice-title { font-size: 28px; color: #333; letter-spacing: 1px; }
        .status-paid {
            color: #22c55e;
            border: 1px solid #22c55e;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-top: 5px;
            text-transform: uppercase;
        }

        /* --- Info Section (Billed To & Invoice Meta) --- */
        .info-table { width: 100%; margin-bottom: 40px; }
        .info-table td { vertical-align: top; }
        .bill-to-title { font-size: 12px; text-transform: uppercase; color: #777; margin-bottom: 5px; font-weight: bold; letter-spacing: 0.5px; }
        .client-name { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px; }

        /* --- Items Table --- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            color: #555;
        }
        .items-table .last-row td { border-bottom: 2px solid #0777be; }

        /* --- Totals Section --- */
        .totals-table {
            width: 40%;
            margin-left: auto; /* Push to right */
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 0;
            text-align: right;
            color: #555;
        }
        .total-row td {
            font-size: 18px;
            font-weight: bold;
            color: #0777be;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        /* --- Footer --- */
        .footer {
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td valign="top">
                @if(!empty($logo))
                    <img src="{{ $logo }}" class="logo" alt="Logo">
                @else
                    <h1 class="text-brand">Exam Babu</h1>
                @endif
            </td>
            <td class="text-right" valign="top">
                <div class="invoice-title">INVOICE</div>
                <div class="status-paid">PAID</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="60%">
                <div class="bill-to-title">Billed To</div>
                <div class="client-name">{{ $payment->user->name ?? $payment->user->first_name }}</div>
                
                <div class="text-gray text-sm">
                    @if(isset($data['customer_billing_information']))
                        {{ $data['customer_billing_information']['address'] ?? '' }}<br>
                        {{ $data['customer_billing_information']['city'] ?? '' }}
                        @if(!empty($data['customer_billing_information']['zip']))
                            - {{ $data['customer_billing_information']['zip'] }}
                        @endif
                        <br>
                        Phone: {{ $data['customer_billing_information']['phone'] ?? 'N/A' }}
                    @else
                        {{ $payment->user->email }}<br>
                        {{ $payment->user->mobile ?? '' }}
                    @endif
                </div>
            </td>

            <td width="40%" class="text-right">
                <table width="100%">
                    <tr>
                        <td class="text-right text-sm font-bold text-gray" style="padding-bottom: 5px;">Invoice ID:</td>
                        <td class="text-right text-sm" style="padding-bottom: 5px;">#{{ $payment->invoice_id ?? str_pad((string)$payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right text-sm font-bold text-gray" style="padding-bottom: 5px;">Date:</td>
                        <td class="text-right text-sm" style="padding-bottom: 5px;">{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-right text-sm font-bold text-gray">Trans. ID:</td>
                        <td class="text-right text-sm">{{ substr($payment->transaction_id ?? $payment->payment_id, 0, 18) }}...</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="50%">Description</th>
                <th width="20%" class="text-center">Duration</th>
                <th width="30%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr class="last-row">
                <td>
                    <div style="font-weight: bold; color: #333;">{{ $payment->plan->name ?? 'Subscription Plan' }}</div>
                    <div class="text-xs text-light">Access to Exam Series & Premium Content</div>
                </td>
                <td class="text-center">{{ $payment->plan->duration ?? 12 }} Months</td>
                <td class="text-right font-bold">{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals-table">
        @if(isset($data['order_summary']['sub_total']))
        <tr>
            <td>Subtotal:</td>
            <td>{{ $payment->currency }} {{ number_format($data['order_summary']['sub_total'], 2) }}</td>
        </tr>
        @endif

        @if(isset($data['order_summary']['taxes']))
            @foreach($data['order_summary']['taxes'] as $tax)
            <tr>
                <td>{{ $tax['name'] }}:</td>
                <td>+ {{ $payment->currency }} {{ number_format($tax['amount'], 2) }}</td>
            </tr>
            @endforeach
        @endif

        <tr class="total-row">
            <td>Total:</td>
            <td>{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <div class="text-gray" style="margin-bottom: 10px;">
            <strong>Terms & Conditions:</strong><br>
            This is a computer-generated invoice and does not require a physical signature. Payment is non-refundable.
        </div>
        <div class="text-center text-light text-xs">
            &copy; {{ date('Y') }} Exam Babu. All rights reserved. | Need Help? support@exambabu.com
        </div>
    </div>

</body>
</html>