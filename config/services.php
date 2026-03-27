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

    'thermal_printer' => [
        'enabled' => (bool) env('THERMAL_PRINTER_ENABLED', false),
        'ip' => env('THERMAL_PRINTER_IP', '192.168.1.100'),
        'port' => (int) env('THERMAL_PRINTER_PORT', 8008),
        'device_id' => env('THERMAL_PRINTER_DEVICE_ID', 'local_printer'),
    ],

    'minimax' => [
        'api_key' => env('MINIMAX_API_KEY'),
        'voice_id' => env('MINIMAX_VOICE_ID', 'Indonesian_GentleGirl'),
        'model' => env('MINIMAX_MODEL', 'speech-2.8-hd'),
        'strategy' => env('MINIMAX_STRATEGY', 'async'),
        'language_boost' => env('MINIMAX_LANGUAGE_BOOST', 'auto'),
        'speed' => (float) env('MINIMAX_SPEED', 1.0),
        'vol' => (float) env('MINIMAX_VOL', 1.0),
        'pitch' => (int) env('MINIMAX_PITCH', 0),
        'async_poll_attempts' => (int) env('MINIMAX_ASYNC_POLL_ATTEMPTS', 12),
        'async_poll_interval_ms' => (int) env('MINIMAX_ASYNC_POLL_INTERVAL_MS', 500),
        'cache_disk' => env('MINIMAX_CACHE_DISK', 'public'),
        'cache_prefix' => env('MINIMAX_CACHE_PREFIX', 'tts/minimax'),
    ],

];
