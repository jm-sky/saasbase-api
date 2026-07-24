<?php

namespace App\Domain\Subscription\Enums;

enum SubscriptionInvoiceStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case PAID = 'paid';
    case VOID = 'void';
    case UNCOLLECTIBLE = 'uncollectible';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::OPEN => 'Open',
            self::PAID => 'Paid',
            self::VOID => 'Void',
            self::UNCOLLECTIBLE => 'Uncollectible',
            self::FAILED => 'Failed',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function isVoid(): bool
    {
        return $this === self::VOID;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function needsAttention(): bool
    {
        return in_array($this, [self::OPEN, self::UNCOLLECTIBLE, self::FAILED]);
    }
}
