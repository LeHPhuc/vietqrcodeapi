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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vietqr' => [
        'user'       => env('VIETQR_USER'),
        'pass'       => env('VIETQR_PASS'),
        'user_for_vietqr'   => env('USER_FOR_VIETQR'),
        'pass_for_vietqr'   => env('PASS_FOR_VIETQR'),
        'token_url'  => env('VIETQR_TOKEN_URL'),
        'qr_url'     => env('VIETQR_GENERATE_QR_URL'),
        'timeout'    => env('VIETQR_TIMEOUT', 10),
        'jwt_secret' => env('VIETQR_JWT_SECRET'),
        'jwt_ttl'    => env('VIETQR_JWT_TTL'),
    ],

];
