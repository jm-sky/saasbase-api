<?php

namespace App\Domain\Financial\Enums;

enum InvoiceStatus: string
{
    case DRAFT          = 'draft';
    case OCR_PROCESSING = 'ocrProcessing';
    case SENT           = 'sent';
    case PAID           = 'paid';
    case PARTIALLY_PAID = 'partiallyPaid';
    case OVERDUE        = 'overdue';
    case CANCELLED      = 'cancelled';
}
