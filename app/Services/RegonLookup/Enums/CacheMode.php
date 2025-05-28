<?php

namespace App\Services\RegonLookup\Enums;

enum CacheMode: string
{
    case HOURS        = 'hours';
    case END_OF_DAY   = 'end_of_day';
    case END_OF_MONTH = 'end_of_month';
}
