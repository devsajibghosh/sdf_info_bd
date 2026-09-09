      
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>500 - @lang('Server Error')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --text-color: #333;
            --light-text-color: #555;
            --background-color: #f4f7f6;
            --container-bg: #ffffff;
            --icon-color: var(--primary-color);
            --icon-error-color: #e74c3c;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
            overflow-x: hidden;
        }

        .error-container {
            background-color: var(--container-bg);
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            animation: fadeInScaleUp 0.8s ease-out forwards;
        }

        @keyframes fadeInScaleUp {
            0% { opacity: 0; transform: translateY(20px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .icon-wrapper {
            margin-bottom: 30px;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            fill: var(--icon-error-color); /* Using fill for this icon */
            animation: subtleShake 0.5s 3 ease-in-out; /* Shake a few times */
        }
        
        @keyframes subtleShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px) rotate(-2deg); }
            75% { transform: translateX(5px) rotate(2deg); }
        }


        h1 {
            font-size: 2.8em;
            color: var(--secondary-color);
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 700;
            line-height: 1.2;
        }

        .error-title-text {
            font-size: 1.5em;
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1em;
            color: var(--light-text-color);
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .message {
            margin-bottom: 30px;
        }
        
        .action-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: #ffffff;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 10px;
            margin-right: 10px; /* Space between buttons if multiple */
        }
        .action-button:last-child {
            margin-right: 0;
        }

        .action-button:hover {
            background-color: #2980b9; /* Darker shade of primary */
            transform: translateY(-2px);
        }
        .action-button:active {
            transform: translateY(0);
        }

        .brand-logo img {
            max-height: 40px;
            margin-top: 30px;
            opacity: 0.7;
        }
        
        .contact-info {
            font-size: 0.9em;
            color: #777;
            margin-top: 20px;
        }
        .contact-info a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .contact-info a:hover {
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h1 { font-size: 2.3em; }
            .error-title-text { font-size: 1.3em; }
            p { font-size: 1em; }
            .error-icon { width: 70px; height: 70px; }
            .error-container { padding: 30px 20px; }
        }
        @media (max-width: 480px) {
            h1 { font-size: 2em; }
            .error-title-text { font-size: 1.1em; }
            p { font-size: 0.95em; }
            .error-icon { width: 60px; height: 60px; }
            .action-button { display: block; margin: 10px auto 0 auto; } 
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon-wrapper">
            <svg class="error-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 15c-.55 0-1-.45-1-1v-4c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1zm0-8c-.55 0-1-.45-1-1V7c0-.55.45-1 1-1s1 .45 1 1v1c0 .55-.45 1-1 1z"/>
            </svg>
        </div>

        <h1>500</h1>
        <div class="error-title-text">@lang('Internal Server Error')</div>

        <div class="message">
            <p>@lang('Houston, we have a problem! Something went wrong on our end.')</p>
            <p>@lang('Our technical team has been automatically notified. Please try again in a little while.')</p>
        </div>

        <a href="javascript:void(0);" onclick="window.location.reload()" class="action-button">@lang('Try Again')</a>
        <a href="{{ url('/') }}" class="action-button">@lang('Go to Homepage')</a>

        <p class="contact-info">
            @lang('If the problem persists, please') <a href="mailto:{{ generalSetting('site_email') }}">@lang('contact support')</a>.
        </p>

        @if(file_exists(public_path('logo.png'))) 
            <div class="brand-logo">
                <img src="{{ asset('logo.png') }}" alt="Our Brand Logo">
            </div>
        @elseif(file_exists(public_path('images/logo.svg')))
             <div class="brand-logo">
                <img src="{{ asset('images/logo.svg') }}" alt="Our Brand Logo">
            </div>
        @endif
    </div>
</body>
</html>

    