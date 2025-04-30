<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h1>{{ config('app.name') }}</h1>
    </div>

    <div class="content">
        <h2>Verify Your Email Address</h2>

        <p>Hi {{ $notifiable->first_name }},</p>

        <p>Thanks for signing up! Please verify your email address by clicking the button below:</p>

        <div style="text-align: center;">
            <a href="{{ $url }}" class="button" style="color: #ffffff;">
                Verify Email Address
            </a>
        </div>

        <p>If you did not create an account, no further action is required.</p>

        <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>

        <p style="word-break: break-all;">
            {{ $url }}
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
