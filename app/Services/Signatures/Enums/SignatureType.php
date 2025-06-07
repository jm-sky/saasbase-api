<?php

namespace App\Services\Signatures\Enums;

enum SignatureType: string
{
    case XAdES   = 'xades';
    case PAdES   = 'pades';
    case CAdES   = 'cades';
    case ASIC_E  = 'asic-e';
    case UNKNOWN = 'unknown';
}
