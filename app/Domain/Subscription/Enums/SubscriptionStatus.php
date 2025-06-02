<?php

namespace App\Domain\Subscription\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE             = 'active';
    case TRIALING           = 'trialing';
    case PAST_DUE           = 'past_due';
    case CANCELED           = 'canceled';
    case UNPAID             = 'unpaid';
    case INCOMPLETE         = 'incomplete';
    case INCOMPLETE_EXPIRED = 'incomplete_expired';
    case PAUSED             = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE             => 'Active',
            self::TRIALING           => 'Trial',
            self::PAST_DUE           => 'Past Due',
            self::CANCELED           => 'Canceled',
            self::UNPAID             => 'Unpaid',
            self::INCOMPLETE         => 'Incomplete',
            self::INCOMPLETE_EXPIRED => 'Incomplete Expired',
            self::PAUSED             => 'Paused',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::ACTIVE, self::TRIALING]);
    }

    public function isInactive(): bool
    {
        return in_array($this, [self::CANCELED, self::UNPAID, self::INCOMPLETE_EXPIRED]);
    }

    public function needsAttention(): bool
    {
        return in_array($this, [self::PAST_DUE, self::INCOMPLETE]);
    }
}
