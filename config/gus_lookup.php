<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GUS API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the GUS (REGON) API integration.
    |
    */

    'api_url' => env('GUS_API_URL', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc'),

    'user_key' => env('GUS_API_USER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how long the GUS lookup results should be cached.
    | Available modes: 'hours', 'week'
    |
    */

    'cache_mode' => env('GUS_CACHE_MODE', 'hours'),

    'cache_hours' => env('GUS_CACHE_HOURS', 12),
];
