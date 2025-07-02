<?php

namespace App\Domain\Financial\Enums;

enum ApprovalStatus: string
{
    case NOT_REQUIRED    = 'notRequired';
    case PENDING         = 'pending';
    case APPROVED        = 'approved';
    case REJECTED        = 'rejected';
    case CANCELLED       = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NOT_REQUIRED => 'Not Required',
            self::PENDING      => 'Pending Approval',
            self::APPROVED     => 'Approved',
            self::REJECTED     => 'Rejected',
            self::CANCELLED    => 'Cancelled',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::NOT_REQUIRED => 'Nie Wymagane',
            self::PENDING      => 'Oczekuje na Zatwierdzenie',
            self::APPROVED     => 'Zatwierdzone',
            self::REJECTED     => 'Odrzucone',
            self::CANCELLED    => 'Anulowane',
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::APPROVED, self::REJECTED, self::CANCELLED => true,
            self::NOT_REQUIRED, self::PENDING => false,
        };
    }

    public function canTransitionTo(ApprovalStatus $newStatus): bool
    {
        return match ($this) {
            self::NOT_REQUIRED => in_array($newStatus, [
                self::PENDING, self::CANCELLED,
            ], true),
            self::PENDING => in_array($newStatus, [
                self::APPROVED, self::REJECTED, self::CANCELLED,
            ], true),
            self::APPROVED, self::REJECTED, self::CANCELLED => false, // Final states
        };
    }
}
