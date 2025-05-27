<?php

namespace App\Services\GusLookup\Enums;

enum CacheMode: string
{
    case HOURS = 'hours';
    case WEEK  = 'week';
    case MONTH = 'month';
}
