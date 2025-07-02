<?php

namespace App\Domain\Financial\Enums;

enum InvoiceStatus: string
{
    case DRAFT      = 'draft';
    case PROCESSING = 'processing';  // General processing state (OCR, allocation, approval)
    case READY      = 'ready';       // Ready for delivery/sending
    case ACTIVE     = 'active';      // Sent and active
    case COMPLETED  = 'completed';   // Fully paid
    case CANCELLED  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT      => 'Draft',
            self::PROCESSING => 'Processing',
            self::READY      => 'Ready',
            self::ACTIVE     => 'Active',
            self::COMPLETED  => 'Completed',
            self::CANCELLED  => 'Cancelled',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::DRAFT      => 'Szkic',
            self::PROCESSING => 'W Trakcie Przetwarzania',
            self::READY      => 'Gotowe',
            self::ACTIVE     => 'Aktywne',
            self::COMPLETED  => 'Zakończone',
            self::CANCELLED  => 'Anulowane',
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::COMPLETED, self::CANCELLED => true,
            default => false,
        };
    }

    public function canTransitionTo(InvoiceStatus $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newStatus, [
                self::PROCESSING, self::CANCELLED,
            ], true),
            self::PROCESSING => in_array($newStatus, [
                self::READY, self::DRAFT, self::CANCELLED,
            ], true),
            self::READY => in_array($newStatus, [
                self::ACTIVE, self::PROCESSING, self::CANCELLED,
            ], true),
            self::ACTIVE => in_array($newStatus, [
                self::COMPLETED, self::CANCELLED,
            ], true),
            self::COMPLETED, self::CANCELLED => false, // Final states
        };
    }

    /**
     * Check if the invoice is in a state where it can be processed.
     */
    public function canBeProcessed(): bool
    {
        return !$this->isCompleted();
    }

    /**
     * Check if the invoice can be sent/delivered.
     */
    public function canBeSent(): bool
    {
        return self::READY === $this;
    }
}
