<?php

namespace App\Domain\Financial\Enums;

enum DeliveryStatus: string
{
    case NOT_SENT    = 'notSent';
    case PENDING     = 'pending';
    case SENT        = 'sent';
    case DELIVERED   = 'delivered';
    case FAILED      = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NOT_SENT  => 'Not Sent',
            self::PENDING   => 'Pending Send',
            self::SENT      => 'Sent',
            self::DELIVERED => 'Delivered',
            self::FAILED    => 'Failed',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::NOT_SENT  => 'Nie Wysłane',
            self::PENDING   => 'Oczekuje na Wysyłkę',
            self::SENT      => 'Wysłane',
            self::DELIVERED => 'Dostarczone',
            self::FAILED    => 'Nieudane',
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::SENT, self::DELIVERED => true,
            self::NOT_SENT, self::PENDING, self::FAILED => false,
        };
    }

    public function canTransitionTo(DeliveryStatus $newStatus): bool
    {
        return match ($this) {
            self::NOT_SENT => in_array($newStatus, [
                self::PENDING, self::SENT, self::FAILED,
            ], true),
            self::PENDING => in_array($newStatus, [
                self::SENT, self::FAILED, self::NOT_SENT,
            ], true),
            self::SENT => in_array($newStatus, [
                self::DELIVERED, self::FAILED,
            ], true),
            self::DELIVERED => false, // Final state
            self::FAILED    => in_array($newStatus, [
                self::PENDING, self::SENT,
            ], true),
        };
    }
}
