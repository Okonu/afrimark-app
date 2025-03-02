<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Business Email</title>
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
        <h1>Verify Your Business Email</h1>
    </div>

    <p>Hello {{ $business->name }},</p>

    <p>Thank you for registering your business on our platform. To complete your registration and ensure the security of your account, please verify your email address by clicking the button below:</p>

    <div class="btn-container">
        <a href="{{ route('business.verification.verify', ['token' => $token]) }}">Verify Business Email</a>
    </div>

    <p>This verification link will expire in 24 hours.</p>

    <p>If you did not register your business on our platform, please ignore this email.</p>

    <div class="footer">
        <p>Thanks,<br>{{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>
