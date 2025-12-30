<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $payment->invoice_id ?? $payment->id }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            margin-top: 3cm;
            margin-bottom: 2cm;
            margin-left: 2cm;
            margin-right: 2cm;
            color: #334155; /* Slate-700 */
            line-height: 1.5;
        }

        /* Brand Colors */
        .text-brand { color: #0777be; }
        .bg-brand { background-color: #0777be; color: white; }
        .border-brand { border-color: #0777be; }

        /** Header **/
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.5cm 2cm;
            display: block;
        }

        /** Footer **/
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            line-height: 1.5cm;
            font-size: 10px;
            color: #64748b;
        }

        /* Layout Helpers */
        .w-full { width: 100%; }
        .w-half { width: 50%; }
        .float-left { float: left; }
        .float-right { float: right; }
        .clearfix::after { content: ""; clear: both; display: table; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; padding: 10px; background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; color: #475569; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .total-row td { border-top: 2px solid #0777be; font-weight: bold; font-size: 16px; color: #0777be; }

        /* Invoice Meta */
        .invoice-meta { margin-bottom: 30px; }
        .invoice-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            background-color: #22c55e; /* Green */
        }
    </style>
</head>
<body>

    <header>
        <div class="clearfix">
            <div class="float-left">
                @if($logo)
                    <img src="{{ $logo }}" style="height: 40px; margin-top: 10px;" alt="Logo">
                @else
                    <h1 style="margin: 0; color: #0777be;">Exam Babu</h1>
                @endif
            </div>
            <div class="float-right text-right">
                <h2 style="margin: 0; font-size: 24px; color: #0f172a;">INVOICE</h2>
                <span class="invoice-status">Paid</span>
            </div>
        </div>
    </header>

    <main>
        <div class="clearfix invoice-meta">
            <div class="float-left w-half">
                <p class="font-bold uppercase text-brand" style="margin-bottom: 5px; font-size: 11px;">Billed To:</p>
                <h3 style="margin: 0; margin-bottom: 5px;">{{ $payment->user->name ?? $payment->user->first_name }}</h3>

                @if(isset($data['customer_billing_information']))
                    <p style="margin: 0; font-size: 12px; color: #64748b;">
                        {{ $data['customer_billing_information']['address'] ?? '' }}<br>
                        {{ $data['customer_billing_information']['city'] ?? '' }}, {{ $data['customer_billing_information']['state'] ?? '' }} - {{ $data['customer_billing_information']['zip'] ?? '' }}<br>
                        {{ $data['customer_billing_information']['country'] ?? '' }}<br>
                        Phone: {{ $data['customer_billing_information']['phone'] ?? '' }}
                    </p>
                @else
                    <p style="margin: 0; font-size: 12px; color: #64748b;">{{ $payment->user->email }}</p>
                @endif
            </div>

            <div class="float-right text-right w-half">
                <p style="margin: 0; margin-bottom: 2px;"><strong>Invoice ID:</strong> #{{ $payment->invoice_id ?? str_pad((string)$payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p style="margin: 0; margin-bottom: 2px;"><strong>Transaction ID:</strong> {{ $payment->transaction_id ?? $payment->payment_id }}</p>
                <p style="margin: 0;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->created_at)->format('d M, Y') }}</p>
            </div>
        </div>

        <table style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 20%; text-align: center;">Duration</th>
                    <th style="width: 30%; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $payment->plan->name ?? 'Subscription Plan' }}</strong><br>
                        <span style="font-size: 11px; color: #64748b;">Access to Exam Series</span>
                    </td>
                    <td style="text-align: center;">{{ $payment->plan->duration ?? 12 }} Months</td>
                    <td style="text-align: right;">
                        {{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="clearfix">
            <div class="float-right" style="width: 40%;">
                <table style="width: 100%;">
                    @if(isset($data['order_summary']['sub_total']))
                        <tr>
                            <td style="border: none; padding: 5px; color: #64748b;">Subtotal:</td>
                            <td style="border: none; padding: 5px; text-align: right;">
                                {{ $payment->currency }} {{ number_format($data['order_summary']['sub_total'], 2) }}
                            </td>
                        </tr>
                    @endif

                    @if(isset($data['order_summary']['discount_amount']) && $data['order_summary']['discount_amount'] > 0)
                        <tr>
                            <td style="border: none; padding: 5px; color: #16a34a;">Discount:</td>
                            <td style="border: none; padding: 5px; text-align: right; color: #16a34a;">
                                - {{ $payment->currency }} {{ number_format($data['order_summary']['discount_amount'], 2) }}
                            </td>
                        </tr>
                    @endif

                    @if(isset($data['order_summary']['taxes']))
                        @foreach($data['order_summary']['taxes'] as $tax)
                            <tr>
                                <td style="border: none; padding: 5px; color: #64748b;">{{ $tax['name'] }}:</td>
                                <td style="border: none; padding: 5px; text-align: right;">
                                    + {{ $payment->currency }} {{ number_format($tax['amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <tr class="total-row">
                        <td style="padding-top: 10px;">Total:</td>
                        <td style="padding-top: 10px; text-align: right;">
                            {{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="margin-top: 40px; padding: 15px; background-color: #f8fafc; border-radius: 5px; font-size: 12px; color: #64748b;">
            <strong>Terms & Conditions:</strong>
            <p style="margin-top: 5px;">
                This is a computer-generated invoice and does not require a physical signature.
                Payment is non-refundable. For any queries, please contact support@exambabu.com.
            </p>
        </div>
    </main>

    <footer>
        {{ $footer ?? 'Thank you for learning with Exam Babu!' }}
    </footer>

</body>
</html>
