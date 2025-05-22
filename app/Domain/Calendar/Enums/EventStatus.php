<?php

namespace App\Domain\Calendar\Enums;

enum EventStatus: string
{
    case SCHEDULED = 'scheduled';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case TENTATIVE = 'tentative';
}
