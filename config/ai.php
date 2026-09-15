<?php

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],
    'insights' => [
        'regeneration_cooldown_minutes' => (int) env('AI_INSIGHT_COOLDOWN_MINUTES', 60),
    ],
];
