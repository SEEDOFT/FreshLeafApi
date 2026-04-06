<?php

return [
    'default' => env('AI_PROVIDER', 'gemini'),

    'fallbacks' => array_values(array_filter(array_map(
        static fn (string $provider): string => trim($provider),
        explode(',', (string) env('AI_FALLBACK_PROVIDERS', ''))
    ))),

    'providers' => [
        'zen' => [
            'api_key' => env('ZEN_API_KEY'),
            'base_url' => env('ZEN_BASE_URL', 'https://opencode.ai/zen/v1'),
            'model' => env('ZEN_MODEL', 'minimax-m2.5-free'),
            'timeout' => (int) env('ZEN_TIMEOUT', 40),
        ],

        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
            'model' => env('OLLAMA_MODEL', 'qwen2.5:1.5b'),
            'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
        ],
    ],
];
