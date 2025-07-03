<?php

namespace App\Domain\Financial\Enums;

enum AllocationStatus: string
{
    case NOT_REQUIRED        = 'notRequired';
    case PENDING             = 'pending';
    case PARTIALLY_ALLOCATED = 'partiallyAllocated';
    case FULLY_ALLOCATED     = 'fullyAllocated';

    public function label(): string
    {
        return match ($this) {
            self::NOT_REQUIRED        => 'Not Required',
            self::PENDING             => 'Pending Allocation',
            self::PARTIALLY_ALLOCATED => 'Partially Allocated',
            self::FULLY_ALLOCATED     => 'Fully Allocated',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::NOT_REQUIRED        => 'Nie Wymagane',
            self::PENDING             => 'Oczekuje na Alokację',
            self::PARTIALLY_ALLOCATED => 'Częściowo Przydzielone',
            self::FULLY_ALLOCATED     => 'W Pełni Przydzielone',
        };
    }

    public function isCompleted(): bool
    {
        return self::FULLY_ALLOCATED === $this;
    }

    public function requiresAction(): bool
    {
        return match ($this) {
            self::PENDING, self::PARTIALLY_ALLOCATED => true,
            self::NOT_REQUIRED, self::FULLY_ALLOCATED => false,
        };
    }

    public function canTransitionTo(AllocationStatus $newStatus): bool
    {
        return match ($this) {
            self::NOT_REQUIRED => in_array($newStatus, [
                self::PENDING,
            ], true),
            self::PENDING => in_array($newStatus, [
                self::PARTIALLY_ALLOCATED, self::FULLY_ALLOCATED,
            ], true),
            self::PARTIALLY_ALLOCATED => in_array($newStatus, [
                self::FULLY_ALLOCATED, self::PENDING,
            ], true),
            self::FULLY_ALLOCATED => in_array($newStatus, [
                self::PARTIALLY_ALLOCATED, self::PENDING,
            ], true),
        };
    }
}
