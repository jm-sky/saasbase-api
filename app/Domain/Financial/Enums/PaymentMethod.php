<?php

namespace App\Domain\Financial\Enums;

enum PaymentMethod: string
{
    case BANK_TRANSFER = 'bankTransfer';
    case CASH          = 'cash';
    case CREDIT_CARD   = 'creditCard';
    case PAYPAL        = 'payPal';
    case OTHER         = 'other';
}
