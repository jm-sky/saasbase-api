<?php

namespace App\Domain\Billing\Enums;

enum BillingPeriod: string
{
    case MONTHLY = 'monthly';
    case YEARLY  = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::YEARLY  => 'Yearly',
        };
    }
}
