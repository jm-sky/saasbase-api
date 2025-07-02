<?php

namespace App\Domain\Expense\Enums;

enum ExpenseAllocationStatus: string
{
    case PENDING   = 'pending';
    case ALLOCATED = 'allocated';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::ALLOCATED => 'Allocated',
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Rejected',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::PENDING   => 'Oczekujące',
            self::ALLOCATED => 'Przydzielone',
            self::APPROVED  => 'Zatwierdzone',
            self::REJECTED  => 'Odrzucone',
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::APPROVED, self::REJECTED => true,
            self::PENDING, self::ALLOCATED => false,
        };
    }

    public function canTransitionTo(ExpenseAllocationStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING   => in_array($newStatus, [self::ALLOCATED, self::REJECTED], true),
            self::ALLOCATED => in_array($newStatus, [self::APPROVED, self::REJECTED], true),
            self::APPROVED, self::REJECTED => false, // Final states
        };
    }
}
