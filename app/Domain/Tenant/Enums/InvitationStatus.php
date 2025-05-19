<?php

namespace App\Domain\Tenant\Enums;

enum InvitationStatus: string
{
    case PENDING  = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED  = 'expired';
    case CANCELED = 'canceled';
}
