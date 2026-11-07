<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_SERVICE_KEY'),
        'timeout' => env('SUPABASE_TIMEOUT', 10),
        'connect_timeout' => env('SUPABASE_CONNECT_TIMEOUT', 3),
    ],

    'ai' => [
        'free_access' => (bool) env('AI_FREE_FOR_ALL', false),
        'base_url' => env(
            'AI_BASE_URL',
            env('GEMINI_API_KEY')
                ? env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai')
                : (env('GROQ_API_KEY')
                    ? env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1')
                    : 'http://127.0.0.1:11434/v1')
        ),
        'key' => env('AI_API_KEY', env('GEMINI_API_KEY', env('GROQ_API_KEY'))),
        'model' => env(
            'AI_MODEL',
            env('GEMINI_API_KEY')
                ? env('GEMINI_MODEL', 'gemini-2.5-flash')
                : (env('GROQ_API_KEY')
                    ? env('GROQ_MODEL', 'llama-3.3-70b-versatile')
                    : 'llama3.2')
        ),
        'timeout' => env('AI_TIMEOUT', 30),
        'connect_timeout' => env('AI_CONNECT_TIMEOUT', 5),
        'temperature' => env('AI_TEMPERATURE', 0.2),
    ],

    'gemini' => [
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai'),
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'free_models' => [
            'gemini-2.5-flash' => 'Google Gemini 2.5 Flash (15 RPM / 1M TPM / 1,500 RPD Free)',
            'gemini-1.5-flash' => 'Google Gemini 1.5 Flash (Fast Multimodal)',
            'gemini-1.5-pro' => 'Google Gemini 1.5 Pro (Deep Academic Reasoning)',
        ],
    ],

    'groq' => [
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'free_models' => [
            'llama-3.3-70b-versatile' => 'Llama 3.3 70B',
            'deepseek-r1-distill-70b' => 'DeepSeek-R1 Distill 70B',
            'qwen/qwen3-32b' => 'Qwen 2.5/3 32B',
        ],
    ],

    'camber' => [
        'agent_url' => env('CAMBER_AGENT_URL', 'https://app.cambercloud.com/agents/hafizurrahman78232878/Thesis_Copilot'),
        'base_url' => env('CAMBER_BASE_URL', 'https://app.cambercloud.com/api/v1'),
        'key' => env('CAMBER_API_KEY'),
        'model' => env('CAMBER_MODEL', 'Thesis_Copilot'),
        'free_models' => [
            'Thesis_Copilot' => 'Alice · Thesis Co-Pilot (Camber Cloud)',
        ],
    ],

    'mcp' => [
        'access_token' => env('MCP_ACCESS_TOKEN'),
    ],

];
