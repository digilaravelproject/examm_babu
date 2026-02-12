<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563EB;
        }

        .invoice-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .invoice-box th,
        .invoice-box td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .invoice-box th {
            bg-color: #f9f9f9;
        }

        .total {
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .status {
            padding: 5px 10px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="company-name">{{ $company_name }}</div>
        <div>{{ $company_address }}</div>
        <h3>INVOICE RECEIPT</h3>
    </div>

    <p><strong>Billed To:</strong><br>
        {{ $subscription->user->first_name }} {{ $subscription->user->last_name }}<br>
        {{ $subscription->user->email }}</p>

    <p><strong>Invoice Date:</strong> {{ $date }}<br>
        <strong>Payment ID:</strong> {{ $subscription->payment_id }}
    </p>

    <table class="invoice-box">
        <thead>
            <tr>
                <th>Description</th>
                <th>Plan Duration</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $subscription->plan->name }} Subscription</td>
                <td>{{ $subscription->plan->duration }} Months</td>
                <td>Paid</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        Total Status: <span class="status">PAID</span>
    </div>

    <p style="margin-top: 50px; font-size: 12px; color: gray; text-align: center;">
        This is a computer-generated invoice and does not require a signature.
    </p>

</body>

</html>
