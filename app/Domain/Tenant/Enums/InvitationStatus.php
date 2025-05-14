<?php

namespace App\Domain\Tenant\Enums;

enum InvitationStatus: string
{
    case PENDING  = 'pending';
    case ACCEPTED = 'accepted';
    case EXPIRED  = 'expired';
}
