<?php

namespace App\Domain\Financial\Enums;

enum ResetPeriod: string
{
    case NEVER   = 'never';
    case YEARLY  = 'yearly';
    case MONTHLY = 'monthly';
}
