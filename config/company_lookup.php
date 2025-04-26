<?php

return [
    'cache_hours' => env('COMPANY_LOOKUP_CACHE_HOURS', 12),
    'cache_mode'  => env('COMPANY_LOOKUP_CACHE_MODE', 'hours'), // 'hours' albo 'week'
];
