<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Health Details Token
    |--------------------------------------------------------------------------
    |
    | Bearer token required to access GET /api/health/details (Ops Monitor).
    | When empty, the endpoint is disabled and always returns 401.
    |
    */

    'details_token' => env('HEALTH_DETAILS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Component Check Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait for each component probe. The overall response
    | must stay under Ops Monitor's 5-second limit.
    |
    */

    'check_timeout_seconds' => (int) env('HEALTH_CHECK_TIMEOUT_SECONDS', 3),
];
