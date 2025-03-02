<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Important: Your Business Has Been Listed as a Debtor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #dc3545;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
        }
        .warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $appName }}</h1>
        <h2>Important Notice: Debt Listing</h2>
    </div>

    <div class="content">
        <p>Dear {{ $debtor->name }},</p>

        <p><strong>{{ $businessName }}</strong> has listed your business as a debtor in our system for the amount of <strong>KES {{ $amountOwed }}</strong>.</p>

        <div class="info-box">
            <p><strong>Invoice Number:</strong> {{ $invoiceNumber }}</p>
            <p><strong>Amount Owed:</strong> KES {{ $amountOwed }}</p>
            <p><strong>Listing Date:</strong> {{ now()->format('j F Y') }}</p>
        </div>

        <p class="warning">This listing will become publicly visible in 7 days unless it is resolved or disputed.</p>

        <p>If you believe this is an error or would like to dispute this claim, you have two options:</p>

        <ol>
            <li>
                <strong>If you already have an account:</strong><br>
                <a href="{{ $disputeUrl }}" class="button">Login & Dispute This Listing</a>
            </li>
            <li>
                <strong>If you don't have an account:</strong><br>
                <a href="{{ $registrationUrl }}" class="button">Register & Dispute This Listing</a>
            </li>
        </ol>

        <p>If you choose to register, your business information (name, email, and KRA PIN) will be pre-filled based on the listing details and cannot be modified. This ensures that your dispute is correctly linked to this listing.</p>

        <p>If you have any questions, please don't hesitate to contact our support team at <a href="mailto:support@example.com">support@example.com</a>.</p>
    </div>

    <div class="footer">
        <p>This is an automated message from {{ $appName }}. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
