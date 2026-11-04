<?php

return [
    'key' => env('GROQ_API_KEY'),
    'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
    'endpoint' => env('GROQ_ENDPOINT', 'https://api.groq.com/openai/v1'),
    'timeout' => (int) env('GROQ_TIMEOUT', 20),
    'retry' => ['times' => 2, 'sleep' => 200],
    'rate_limit' => (int) env('CHATBOT_RATE_LIMIT', 20),
    'max_kb' => (int) env('CHATBOT_MAX_KB_RESULTS', 5),
    'max_db' => (int) env('CHATBOT_MAX_DB_RESULTS', 6),
    'history_turns' => 4,
];
