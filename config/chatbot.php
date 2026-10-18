<?php

return [

    'enabled' => env('CHATBOT_ENABLED', true),

    'knowledge_path' => resource_path('knowledge/sivia'),

    'system_prompt_path' => resource_path('prompts/chatbot_system.txt'),

    'cache_ttl_seconds' => 3600,

    'max_input_chars' => (int) env('CHATBOT_MAX_INPUT_CHARS', 500),

    'max_history_messages' => 6,

    'max_history_chars_per_message' => 500,

    'max_output_chars' => 1200,

    'rate_limit_per_minute' => (int) env('CHATBOT_RATE_LIMIT', 10),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'timeout' => (int) env('CHATBOT_TIMEOUT', 30),
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
        'temperature' => 0.2,
        'thinking_level' => env('GEMINI_THINKING_LEVEL', 'minimal'),
        'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 1024),
    ],

    'messages' => [
        'out_of_scope' => 'Lo siento, solo puedo ayudarte con información relacionada con SIVIA, sus módulos y funcionalidades.',
        'insufficient' => 'No tengo información suficiente sobre eso en SIVIA. Puedes consultar el módulo correspondiente o contactar al administrador del sistema.',
        'unavailable' => 'El asistente no está disponible en este momento. Inténtalo de nuevo más tarde.',
        'timeout' => 'La consulta tardó demasiado. Inténtalo de nuevo en unos segundos.',
        'disabled' => 'El asistente de SIVIA no está disponible en este momento.',
        'too_many' => 'Has enviado demasiadas preguntas seguidas. Espera un momento e inténtalo de nuevo.',
        'too_long' => 'La respuesta es extensa. Pregúntame por una parte específica y te la explico completa.',
    ],

];
