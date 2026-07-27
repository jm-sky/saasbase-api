<?php

namespace App\Domain\Approval\Enums;

enum ApprovalExecutionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::PENDING => 'Oczekuje',
            self::APPROVED => 'Zatwierdzone',
            self::REJECTED => 'Odrzucone',
            self::CANCELLED => 'Anulowane',
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::APPROVED, self::REJECTED, self::CANCELLED => true,
            self::PENDING => false,
        };
    }

    public function canTransitionTo(ApprovalExecutionStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [
                self::APPROVED, self::REJECTED, self::CANCELLED,
            ], true),
            self::APPROVED, self::REJECTED, self::CANCELLED => false, // Final states
        };
    }

    public function isComplete(): bool
    {
        return $this->isCompleted();
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
