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
        'key'          => env('DEEPSEEK_API_KEY'),
        'model'        => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'vision_model' => env('DEEPSEEK_VISION_MODEL', 'deepseek-vl2'),
        // Catalog research client settings (see config/catalog_research.php too).
        'base_url'     => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'timeout'      => (int) env('DEEPSEEK_TIMEOUT', 120),
        'max_retries'  => (int) env('DEEPSEEK_MAX_RETRIES', 3),
        'rate_limit'   => (int) env('DEEPSEEK_RATE_LIMIT', 30), // requests / minute
    ],

    // Google Gemini — free tier at https://aistudio.google.com/apikey
    'gemini' => [
        'key'   => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_VISION_MODEL', 'gemini-2.0-flash'),
    ],

    // Groq — free tier at https://console.groq.com  (vision: llama-4 / llama-3.2)
    'groq' => [
        'key'   => env('GROQ_API_KEY', ''),
        'model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
    ],

    // Generic vision fallback (any OpenAI-compatible endpoint: OpenAI, OpenRouter, etc.)
    'vision' => [
        'key'      => env('VISION_API_KEY', ''),
        'base_url' => env('VISION_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model'    => env('VISION_MODEL', 'google/gemini-flash-1.5-8b'),
    ],

    'ocrspace' => [
        'key' => env('OCRSPACE_API_KEY', 'helloworld'),
    ],

];
