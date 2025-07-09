<?php

namespace App\Domain\Invoice\Enums;

use App\Traits\HasEnumValues;

enum ResetPeriod: string
{
    use HasEnumValues;

    case NEVER   = 'never';
    case YEARLY  = 'yearly';
    case MONTHLY = 'monthly';
}
