<?php

namespace App\Domain\Subscription\Enums;

enum BillingInterval: string
{
    case MONTHLY   = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY    = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY   => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::YEARLY    => 'Yearly',
        };
    }

    public function months(): int
    {
        return match ($this) {
            self::MONTHLY   => 1,
            self::QUARTERLY => 3,
            self::YEARLY    => 12,
        };
    }

    public function days(): int
    {
        return match ($this) {
            self::MONTHLY   => 30,
            self::QUARTERLY => 90,
            self::YEARLY    => 365,
        };
    }
}
