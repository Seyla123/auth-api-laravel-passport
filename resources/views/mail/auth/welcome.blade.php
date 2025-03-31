<!DOCTYPE html>
<html>

<head>
    <title>Welcome to Our Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        .header {
            background: linear-gradient(135deg, #6c5ce7, #a363d9);
            color: white;
            padding: 30px 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            word-wrap: break-word;
        }

        .content {
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .content p {
            margin-bottom: 15px;
            font-size: 16px;
            color: #4a4a4a;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #6c5ce7, #a363d9);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 25px;
            font-weight: 500;
            transition: background-color 0.3s ease;
            font-size: 16px;
            width: fit-content;
        }

        .button:hover {
            background: #34495e;
        }

        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e6e6e6;
            color: #666;
        }

        @media screen and (max-width: 480px) {
            body {
                padding: 10px;
            }

            .header {
                padding: 20px 10px;
            }

            .content {
                padding: 20px;
            }

            .button {
                padding: 10px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <p>Dear {{ $user->name }},</p>

            <p>Thank you for joining {{ config('app.name') }}. We are pleased to inform you that your email has been
                successfully verified and your account is now active.</p>

            <p>Our dedicated support team is available to assist you with any questions or concerns you may have. Please
                don't hesitate to contact us if you need assistance.</p>

            <center>
                <a href="{{ config('app.url') }}/login" class="button">Access Your Account</a>
            </center>

            <div class="signature">
                <p>Best regards,<br>
                    The {{ config('app.name') }} Team</p>
            </div>
        </div>
    </div>
</body>

</html>
