<?php

namespace App\Domain\IdentityCheck\Enums;

enum IdentityCheckMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Edoreczenia  = 'edoreczenia';
    case Epuap        = 'epuap';
    case Manual       = 'manual';
    case KsefToken    = 'ksef_token';
    case Ceidg        = 'ceidg';
}
