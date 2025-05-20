<?php

namespace App\Domain\Invoice\Enums;

enum ResetPeriod: string
{
    case NEVER   = 'never';
    case YEARLY  = 'yearly';
    case MONTHLY = 'monthly';
}
