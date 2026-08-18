<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LITERA Search Weights (Configurable Formula)
    |--------------------------------------------------------------------------
    | Overall Score = (Semantic * weight) + (Keyword * weight) + (Recency * weight) + (Citation * weight)
    */
    'search_weights' => [
        'semantic' => 0.40,
        'keyword'  => 0.30,
        'recency'  => 0.15,
        'citation' => 0.15,
    ],

    /*
    |--------------------------------------------------------------------------
    | LLM API Configuration (Groq / Gemini / OpenAI compatible)
    |--------------------------------------------------------------------------
    */
    'llm' => [
        'api_key'  => env('OPENAI_API_KEY', env('GROQ_API_KEY')),
        'base_url' => env('OPENAI_BASE_URL', env('GROQ_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai/')),
        'model'    => env('OPENAI_MODEL', env('GROQ_MODEL', 'gemini-1.5-flash')),
        'timeout'  => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Academic API Endpoints & Timeouts
    |--------------------------------------------------------------------------
    */
    'apis' => [
        'openalex' => [
            'base_url' => 'https://api.openalex.org',
            'timeout'  => 10,
            'per_page' => 10,
        ],
        'semantic_scholar' => [
            'base_url' => 'https://api.semanticscholar.org/graph/v1',
            'timeout'  => 10,
            'limit'    => 10,
        ],
        'open_library' => [
            'base_url' => 'https://openlibrary.org',
            'timeout'  => 8,
        ],
        'google_books' => [
            'base_url' => 'https://www.googleapis.com/books/v1',
            'timeout'  => 8,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'search_ttl_hours'  => 24,
        'metadata_ttl_days' => 7,
    ],

];
