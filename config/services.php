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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Services
    |--------------------------------------------------------------------------
    |
    | Configuration for OAuth authentication services. These services allow
    | users to authenticate using their existing accounts from these providers.
    |
    */

    'github' => [
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect'      => env('GITHUB_REDIRECT_URI'),
        'scope'         => ['read:user', 'user:email'],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Services
    |--------------------------------------------------------------------------
    |
    | Configuration for AI and language model services.
    |
    */

    'openrouter' => [
        'key'               => env('OPENROUTER_API_KEY'),
        'model'             => env('OPENROUTER_MODEL', 'openai/gpt-3.5-turbo'),
        'url'               => env('OPENROUTER_URL', 'https://openrouter.ai/api/v1/chat/completions'),
        'log'               => env('OPENROUTER_LOG', false),
        'streaming_enabled' => env('OPENROUTER_STREAMING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Verification Services
    |--------------------------------------------------------------------------
    |
    | Configuration for various business verification and lookup services.
    | These services help validate business information and maintain
    | cached results for improved performance.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | IBAN API - International Bank Account Number Validation
    |--------------------------------------------------------------------------
    |
    | Service for validating international bank account numbers (IBAN)
    | and retrieving bank information.
    |
    */
    'ibanapi' => [
        'key'   => env('IBANAPI_KEY'),
        'cache' => [
            'mode'  => env('IBANAPI_CACHE_MODE', 'hours'),
            'hours' => env('IBANAPI_CACHE_HOURS', 12),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Money Forward - Japanese Business Verification
    |--------------------------------------------------------------------------
    |
    | Service for verifying Japanese business information and company details.
    |
    */
    'mf' => [
        'cache' => [
            'mode'  => env('MF_LOOKUP_CACHE_MODE', 'hours'),
            'hours' => env('MF_LOOKUP_CACHE_HOURS', 12),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | VIES - VAT Information Exchange System
    |--------------------------------------------------------------------------
    |
    | European Union service for validating VAT numbers and company information
    | across EU member states.
    |
    */
    'vies' => [
        'cache' => [
            'mode'  => env('VIES_LOOKUP_CACHE_MODE', 'hours'),
            'hours' => env('VIES_LOOKUP_CACHE_HOURS', 12),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | REGON - Polish Business Registry
    |--------------------------------------------------------------------------
    |
    | Polish national business registry service for validating company
    | information and registration numbers.
    |
    */
    'regon' => [
        'api_url'    => env('REGON_API_URL', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc'),
        'user_key'   => env('REGON_API_USER_KEY'),
        'should_log' => env('REGON_SHOULD_LOG', false),
        'cache'      => [
            'mode'  => env('REGON_CACHE_MODE', 'hours'),
            'hours' => env('REGON_CACHE_HOURS', 12),
        ],
    ],
];
