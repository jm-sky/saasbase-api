<?php

namespace App\Domain\Financial\Enums;

enum PaymentStatus: string
{
    case PENDING        = 'pending';
    case PARTIALLY_PAID = 'partiallyPaid';
    case PAID           = 'paid';
    case OVERDUE        = 'overdue';
    case CANCELLED      = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING        => 'Pending',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID           => 'Paid',
            self::OVERDUE        => 'Overdue',
            self::CANCELLED      => 'Cancelled',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::PENDING        => 'Oczekuje na Płatność',
            self::PARTIALLY_PAID => 'Częściowo Opłacone',
            self::PAID           => 'Opłacone',
            self::OVERDUE        => 'Przeterminowane',
            self::CANCELLED      => 'Anulowane',
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::PAID, self::CANCELLED => true,
            self::PENDING, self::PARTIALLY_PAID, self::OVERDUE => false,
        };
    }

    public function requiresAction(): bool
    {
        return match ($this) {
            self::PENDING, self::PARTIALLY_PAID, self::OVERDUE => true,
            self::PAID, self::CANCELLED => false,
        };
    }

    public function canTransitionTo(PaymentStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [
                self::PARTIALLY_PAID, self::PAID, self::OVERDUE, self::CANCELLED,
            ], true),
            self::PARTIALLY_PAID => in_array($newStatus, [
                self::PAID, self::OVERDUE, self::CANCELLED,
            ], true),
            self::OVERDUE => in_array($newStatus, [
                self::PARTIALLY_PAID, self::PAID, self::CANCELLED,
            ], true),
            self::PAID, self::CANCELLED => false, // Final states
        };
    }
}
