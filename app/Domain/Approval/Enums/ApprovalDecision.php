<?php

namespace App\Domain\Approval\Enums;

enum ApprovalDecision: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::APPROVED => 'Zatwierdzone',
            self::REJECTED => 'Odrzucone',
        };
    }

    public function isPositive(): bool
    {
        return self::APPROVED === $this;
    }

    public function isNegative(): bool
    {
        return self::REJECTED === $this;
    }
}
