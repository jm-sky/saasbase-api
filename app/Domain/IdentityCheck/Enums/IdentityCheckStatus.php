<?php

namespace App\Domain\IdentityCheck\Enums;

enum IdentityCheckStatus: string
{
    case Pending  = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
