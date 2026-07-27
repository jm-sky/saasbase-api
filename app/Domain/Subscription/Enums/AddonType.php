<?php

namespace App\Domain\Subscription\Enums;

enum AddonType: string
{
    case ONE_TIME = 'one_time';
    case RECURRING = 'recurring';
    case USAGE_BASED = 'usage_based';

    public function label(): string
    {
        return match ($this) {
            self::ONE_TIME => 'One Time',
            self::RECURRING => 'Recurring',
            self::USAGE_BASED => 'Usage Based',
        };
    }

    public function isRecurring(): bool
    {
        return $this === self::RECURRING;
    }

    public function isOneTime(): bool
    {
        return $this === self::ONE_TIME;
    }

    public function isUsageBased(): bool
    {
        return $this === self::USAGE_BASED;
    }
}
