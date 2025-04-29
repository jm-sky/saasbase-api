<?php

return [
    'cache_hours' => env('VIES_LOOKUP_CACHE_HOURS', 12),
    'cache_mode'  => env('VIES_LOOKUP_CACHE_MODE', 'hours'), // 'hours' or 'week'
];
