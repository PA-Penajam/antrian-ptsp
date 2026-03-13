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
        'port' => (int) env('THERMAL_PRINTER_PORT', 8043),
        'device_id' => env('THERMAL_PRINTER_DEVICE_ID', 'local_printer'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID'),
        'model' => env('ELEVENLABS_MODEL', 'eleven_turbo_v2_5'),
        'stability' => (float) env('ELEVENLABS_STABILITY', 0.45),
        'similarity_boost' => (float) env('ELEVENLABS_SIMILARITY_BOOST', 0.8),
        'style' => (float) env('ELEVENLABS_STYLE', 0.2),
        'use_speaker_boost' => (bool) env('ELEVENLABS_USE_SPEAKER_BOOST', true),
        'cache_disk' => env('ELEVENLABS_CACHE_DISK', 'public'),
        'cache_prefix' => env('ELEVENLABS_CACHE_PREFIX', 'tts/elevenlabs'),
    ],

];
