<?php

namespace App\Domain\Invoice\Enums;

enum InvoicePaymentStatus: string
{
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case OVERDUE   = 'overdue';
    case CANCELLED = 'cancelled';
}
