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

    'ai_quotation' => [
        'base_url'        => env('AI_QUOTATION_BASE_URL', ''),
        'parse_endpoint'  => env('AI_QUOTATION_PARSE_ENDPOINT', 'parse'),
        'api_key'         => env('AI_QUOTATION_API_KEY', ''),
        'timeout'         => env('AI_QUOTATION_TIMEOUT', 30),
        'test_mode'       => env('AI_QUOTATION_TEST_MODE', false),
    ],

    'deepseek' => [
        'key'   => env('DEEPSEEK_API_KEY'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    // Vision-capable model (any OpenAI-compatible endpoint: OpenAI, OpenRouter, Gemini, etc.)
    // DeepSeek does NOT support image inputs — use a vision API here.
    // Free option: OpenRouter (https://openrouter.ai) with google/gemini-flash-1.5-8b
    // Cheap option: OpenAI gpt-4o-mini  |  Google Gemini Flash (https://ai.google.dev)
    'vision' => [
        'key'      => env('VISION_API_KEY', ''),
        'base_url' => env('VISION_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model'    => env('VISION_MODEL', 'google/gemini-flash-1.5-8b'),
    ],

    'ocrspace' => [
        'key' => env('OCRSPACE_API_KEY', 'helloworld'),
    ],

];
