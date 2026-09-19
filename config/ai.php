<?php

return [
    'provider' => env('AI_PROVIDER', 'gemini'),
    'key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL', 'gemini-3.6-flash'),
    'base_url' => rtrim(env('AI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
    'timeout' => (int) env('AI_TIMEOUT', 30),
    'auto_reply_delay_minutes' => (int) env('AI_AUTO_REPLY_DELAY_MINUTES', 5),
];
