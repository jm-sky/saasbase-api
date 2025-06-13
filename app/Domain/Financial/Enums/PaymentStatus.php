<?php

namespace App\Domain\Financial\Enums;

enum PaymentStatus: string
{
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case OVERDUE   = 'overdue';
    case CANCELLED = 'cancelled';
}
