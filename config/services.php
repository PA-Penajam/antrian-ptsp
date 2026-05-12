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

    'google_tts' => [
        'api_key' => env('GOOGLE_TTS_API_KEY'),
        'language_code' => env('GOOGLE_TTS_LANGUAGE_CODE', 'id-ID'),
        'voice_name' => env('GOOGLE_TTS_VOICE_NAME', 'id-ID-Wavenet-A'),
        'speaking_rate' => (float) env('GOOGLE_TTS_SPEAKING_RATE', 1.0),
        'pitch' => (float) env('GOOGLE_TTS_PITCH', 0.0),
        'volume_gain_db' => (float) env('GOOGLE_TTS_VOLUME_GAIN_DB', 0.0),
        'cache_disk' => env('GOOGLE_TTS_CACHE_DISK', 'public'),
        'cache_prefix' => env('GOOGLE_TTS_CACHE_PREFIX', 'tts/google'),
        'legacy_voice_names' => array_filter(explode(',', env('GOOGLE_TTS_LEGACY_VOICE_NAMES', 'id-ID-Standard-D,id-ID-Wavenet-A'))),
    ],

];
