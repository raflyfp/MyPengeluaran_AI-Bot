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

    'telegram' => [
        // Token bot dan email user default dipakai saat Telegram webhook memproses pesan masuk.
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
        'default_user_email' => env('TELEGRAM_DEFAULT_USER_EMAIL', env('BOT_DEFAULT_USER_EMAIL')),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'gemini' => [
        // API key dan model Gemini dipakai oleh GeminiFinanceMessageParser.
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
    ],

];
