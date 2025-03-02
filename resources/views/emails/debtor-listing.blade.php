<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You Have Been Listed as a Debtor</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding: 0;
            margin: 0;
            color: #333;
            line-height: 1.5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        h1 {
            color: #222;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        p {
            margin: 20px 0;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #dc8b15;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>You Have Been Listed as a Debtor</h1>
    </div>

    <p>Dear Business Owner,</p>

    <p>This is to inform you that your business has been listed as a debtor on the Afrimark Business Portal by:</p>

    <p>
        <strong>Business Name:</strong> {{ $debtor->business->name ?? 'A business on our platform' }}<br>
        <strong>Outstanding Amount:</strong> {{ number_format($debtor->amount_owed, 2) }} KES<br>
        <strong>Invoice Number:</strong> {{ $debtor->invoice_number ?? 'N/A' }}
    </p>

    <p><strong>Important Notice:</strong> This listing will become publicly visible in 7 days unless resolved.</p>

    <div class="btn-container">
        <a href="{{ route('debtor.dispute', ['id' => $debtor->id]) }}" class="btn">Dispute This Listing</a>
    </div>

    <p>If you believe this listing is incorrect, please click the button above to submit a dispute. You will need to create an account or log in to proceed.</p>

    <p>If you have any questions, please contact us directly.</p>

    <div class="footer">
        <p>Thanks,<br>{{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>
