<?php

namespace App\Domain\Subscription\Enums;

enum SubscriptionInvoiceStatus: string
{
    case DRAFT         = 'draft';
    case OPEN          = 'open';
    case PAID          = 'paid';
    case VOID          = 'void';
    case UNCOLLECTIBLE = 'uncollectible';
    case FAILED        = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT         => 'Draft',
            self::OPEN          => 'Open',
            self::PAID          => 'Paid',
            self::VOID          => 'Void',
            self::UNCOLLECTIBLE => 'Uncollectible',
            self::FAILED        => 'Failed',
        };
    }

    public function isPaid(): bool
    {
        return self::PAID === $this;
    }

    public function isOpen(): bool
    {
        return self::OPEN === $this;
    }

    public function isVoid(): bool
    {
        return self::VOID === $this;
    }

    public function isFailed(): bool
    {
        return self::FAILED === $this;
    }

    public function needsAttention(): bool
    {
        return in_array($this, [self::OPEN, self::UNCOLLECTIBLE, self::FAILED]);
    }
}
