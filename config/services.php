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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'endpoint' => env('BREVO_API_ENDPOINT', 'https://api.brevo.com/v3/smtp/email'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'no-replymyakad@refaadstack.com'),
        'from_name' => env('MAIL_FROM_NAME', 'MyAkad'),
    ],

    'cloudmail' => [
        'enabled' => env('CLOUDMAIL_ENABLED', false),
        'base_url' => env('CLOUDMAIL_BASE_URL', 'https://mail.refaadstack.com/api'),
        'email' => env('CLOUDMAIL_EMAIL'),
        'password' => env('CLOUDMAIL_PASSWORD'),
        'timeout' => env('CLOUDMAIL_TIMEOUT', 20),
        'cache_ttl' => env('CLOUDMAIL_CACHE_TTL', 3600),
        'senders' => [
            'default' => env('CLOUDMAIL_SENDER_DEFAULT'),
            'registration' => env('CLOUDMAIL_SENDER_REGISTRATION'),
            'payment' => env('CLOUDMAIL_SENDER_PAYMENT'),
            'notif' => env('CLOUDMAIL_SENDER_NOTIF'),
        ],
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

    'payment_service' => [
        'base_url' => env('PAYMENT_SERVICE_URL'),
        'public_url' => env('PAYMENT_SERVICE_PUBLIC_URL'),
        'product_key' => env('MYAKAD_PRODUCT_KEY'),
        'callback_secret' => env('MYAKAD_CALLBACK_SECRET'),
    ],

];
