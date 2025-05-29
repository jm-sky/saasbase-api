<?php

return [
    /*
    |--------------------------------------------------------------------------
    | REGON API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the REGON API integration.
    |
    */

    'api_url' => env('REGON_API_URL', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc'),

    'user_key' => env('REGON_API_USER_KEY'),

    'should_log' => env('REGON_SHOULD_LOG', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how long the REGON lookup results should be cached.
    |
    */

    'cache_mode' => env('REGON_CACHE_MODE', 'hours'),

    'cache_hours' => env('REGON_CACHE_HOURS', 12),
];
