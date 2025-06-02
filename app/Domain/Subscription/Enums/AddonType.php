<?php

namespace App\Domain\Subscription\Enums;

enum AddonType: string
{
    case ONE_TIME    = 'one_time';
    case RECURRING   = 'recurring';
    case USAGE_BASED = 'usage_based';

    public function label(): string
    {
        return match ($this) {
            self::ONE_TIME    => 'One Time',
            self::RECURRING   => 'Recurring',
            self::USAGE_BASED => 'Usage Based',
        };
    }

    public function isRecurring(): bool
    {
        return self::RECURRING === $this;
    }

    public function isOneTime(): bool
    {
        return self::ONE_TIME === $this;
    }

    public function isUsageBased(): bool
    {
        return self::USAGE_BASED === $this;
    }
}
