<?php

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],
    'insights' => [
        'regeneration_cooldown_minutes' => (int) env('AI_INSIGHT_COOLDOWN_MINUTES', 60),
    ],
];
