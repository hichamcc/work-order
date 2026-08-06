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

    'mapon' => [
        'key' => env('MAPON_API_KEY'),
        'base_url' => env('MAPON_BASE_URL', 'https://mapon.com/api/v1'),
        // Minutes the unit list stays cached before it is pulled from Mapon again.
        'cache_minutes' => (int) env('MAPON_CACHE_MINUTES', 10),
        // Kilometres a truck may drive after an oil service before it is flagged.
        'oil_service_interval_km' => (int) env('OIL_SERVICE_INTERVAL_KM', 120000),
    ],

];
