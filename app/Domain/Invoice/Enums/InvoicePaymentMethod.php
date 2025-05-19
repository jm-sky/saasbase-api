<?php

namespace App\Domain\Invoice\Enums;

enum InvoicePaymentMethod: string
{
    case BANK_TRANSFER = 'bankTransfer';
    case CASH          = 'cash';
    case CREDIT_CARD   = 'creditCard';
    case PAYPAL        = 'paypal';
    case OTHER         = 'other';
}
