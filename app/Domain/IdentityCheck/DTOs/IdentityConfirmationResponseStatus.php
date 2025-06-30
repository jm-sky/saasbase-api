<?php

namespace App\Domain\IdentityCheck\DTOs;

enum IdentityConfirmationResponseStatus: string
{
    case VERIFIED          = 'verified';
    case UNVERIFIED        = 'unverified';
    case INVALID_XML       = 'invalidXml';
    case INVALID_SIGNATURE = 'invalidSignature';
}
