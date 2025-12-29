<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding: 20px;
            color: #333;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .top-header {
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .title {
            font-size: 30px;
            color: #333;
            font-weight: bold;
        }

        .heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .item td {
            border-bottom: 1px solid #eee;
        }

        .item.last td {
            border-bottom: none;
        }

        .total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
            font-size: 18px;
        }

        .badge {
            background: #d1fae5;
            color: #065f46;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top-header">
                <td class="title">
                    {{ $company_name }}
                </td>
                <td>
                    <strong>Invoice #:</strong> {{ $payment->invoice_no ?? $payment->payment_id }}<br>
                    <strong>Date:</strong> {{ $date }}<br>
                    <strong>Status:</strong> <span class="badge">{{ $payment->status }}</span>
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 40px;">
            <strong>Billed To:</strong><br>
            {{ $payment->user->first_name ?? 'User' }} {{ $payment->user->last_name ?? '' }}<br>
            {{ $payment->user->email }}
        </div>

        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Description</td>
                <td>Amount</td>
            </tr>

            <tr class="item">
                <td>
                    {{ $payment->plan->name ?? 'Subscription Plan' }}<br>
                    <small style="color: #777;">Method: {{ ucfirst($payment->method) }} | Transaction ID:
                        {{ $payment->payment_id }}</small>
                </td>
                <td>
                    {{ $payment->currency ?? 'INR' }} {{ number_format($payment->amount, 2) }}
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td>
                    Total: {{ $payment->currency ?? 'INR' }} {{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        </table>

        <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #aaa;">
            <p>Thank you for your business!</p>
            <p>{{ $company_address }}</p>
        </div>
    </div>
</body>

</html>
