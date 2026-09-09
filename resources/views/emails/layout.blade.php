<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset */
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .email-wrapper {
            width: 100%;
            padding: 20px;
            background-color: #f4f4f7;
        }

        .email-content {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        .email-header {
            background-color: #1a73e8;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .email-body {
            padding: 30px;
            color: #333;
            font-size: 15px;
            line-height: 1.6;
        }

        .email-footer {
            text-align: center;
            font-size: 13px;
            color: #888;
            padding: 20px;
        }

        .btn-primary {
            display: inline-block;
            background-color: #1a73e8;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            font-weight: 600;
        }

        .text-center {
            text-align: center;
        }

        @media (max-width: 600px) {
            .email-body {
                padding: 20px;
            }

            .btn-primary {
                width: 100%;
                box-sizing: border-box;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="email-header">
                <h2>{{ config('app.name') }}</h2>
            </div>
            <div class="email-body">
                @yield('content')
            </div>
            <div class="email-footer">
                &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
