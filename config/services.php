<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    
    'donor_api' => [
    'key' => env('DONOR_API_KEY'),
    ],

    'sms_automation_token' => env('SMS_AUTOMATION_TOKEN', 'MySecretToken123'),
    
    'groq' => [
    'key' => env('GROQ_API_KEY'),
    'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
    ],
    
    
    'bulksms' => [
        'api_key' => env('BULKSMS_API_KEY'),
        'sender_id' => env('BULKSMS_SENDER_ID'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twocheckout' => [
        'merchant' => env('TWOCHECKOUT_MERCHANT_CODE'),
        'secret' => env('TWOCHECKOUT_SECRET_KEY'),
        'sandbox' => env('TWOCHECKOUT_SANDBOX', true),
    ],

    'iyzico' => [
        'api_key'    => env('IYZICO_API_KEY'),
        'secret_key' => env('IYZICO_SECRET_KEY'),
        'base_url'   => env('IYZICO_BASE_URL'),
    ]

];
