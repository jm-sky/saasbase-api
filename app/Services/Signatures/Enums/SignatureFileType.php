<?php

namespace App\Services\Signatures\Enums;

enum SignatureFileType: string
{
    case XML     = 'xml';
    case PDF     = 'pdf';
    case BINARY  = 'binary';
    case ZIP     = 'zip';
    case UNKNOWN = 'unknown';
}
