<?php

return [
    'default' => env('AI_PROVIDER', 'gemini'),

    'fallbacks' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('AI_FALLBACK_PROVIDERS', 'ollama'))
    ))),

    'providers' => [
        'zen' => [
            'api_key' => env('ZEN_API_KEY', ''),
            'base_url' => env('ZEN_BASE_URL', ''),
            'model' => env('ZEN_MODEL', 'zen-free'),
            'timeout' => (int) env('ZEN_TIMEOUT', 40),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
            'model' => env('OLLAMA_MODEL', 'qwen2.5:1.5b'),
            'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
        ],
    ],

    'system_prompt_file' => storage_path('ai-context/system-prompt.md'),
    'project_context_file' => storage_path('ai-context/project-context.md'),

    'language_prompts' => [
        'km' => 'You MUST respond entirely in Khmer (Cambodian) language. Use proper Khmer script (Unicode). Write all responses in Khmer, including greetings, explanations, and recommendations.',
        'en' => 'You should respond in English language.',
    ],

    'fallback_language' => env('AI_FALLBACK_LANGUAGE', 'km'),

    'relevant_topics' => [
        'Product inquiries (vegetables, fruits, herbs, prices, availability)',
        'Order placement and management',
        'Delivery schedules and tracking',
        'Payment methods and payment issues',
        'Account and profile questions',
        'Promotions, discounts, and loyalty programs',
        'Returns, refunds, and customer support',
        'App functionality and technical issues',
        'Store locations and operating hours',
    ],

    'off_topic_response' => 'I\'m here to help with FreshLeaf app-related questions only. For fresh produce orders, delivery, or app support, feel free to ask!',
];
