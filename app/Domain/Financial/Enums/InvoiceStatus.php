<?php

namespace App\Domain\Financial\Enums;

enum InvoiceStatus: string
{
    case DRAFT               = 'draft';
    case OCR_PROCESSING      = 'ocrProcessing';
    case OCR_COMPLETED       = 'ocrCompleted';
    case OCR_FAILED          = 'ocrFailed';
    case PENDING_ALLOCATION  = 'pendingAllocation';
    case ALLOCATED           = 'allocated';
    case PENDING_APPROVAL    = 'pendingApproval';
    case APPROVAL_REJECTED   = 'approvalRejected';
    case APPROVED            = 'approved';
    case SENT                = 'sent';
    case PAID                = 'paid';
    case PARTIALLY_PAID      = 'partiallyPaid';
    case OVERDUE             = 'overdue';
    case CANCELLED           = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT              => 'Draft',
            self::OCR_PROCESSING     => 'OCR Processing',
            self::OCR_COMPLETED      => 'OCR Completed',
            self::OCR_FAILED         => 'OCR Failed',
            self::PENDING_ALLOCATION => 'Pending Allocation',
            self::ALLOCATED          => 'Allocated',
            self::PENDING_APPROVAL   => 'Pending Approval',
            self::APPROVAL_REJECTED  => 'Approval Rejected',
            self::APPROVED           => 'Approved',
            self::SENT               => 'Sent',
            self::PAID               => 'Paid',
            self::PARTIALLY_PAID     => 'Partially Paid',
            self::OVERDUE            => 'Overdue',
            self::CANCELLED          => 'Cancelled',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::DRAFT              => 'Szkic',
            self::OCR_PROCESSING     => 'Przetwarzanie OCR',
            self::OCR_COMPLETED      => 'OCR Zakończone',
            self::OCR_FAILED         => 'OCR Nieudane',
            self::PENDING_ALLOCATION => 'Oczekuje na Alokację',
            self::ALLOCATED          => 'Przydzielone',
            self::PENDING_APPROVAL   => 'Oczekuje na Zatwierdzenie',
            self::APPROVAL_REJECTED  => 'Zatwierdzenie Odrzucone',
            self::APPROVED           => 'Zatwierdzone',
            self::SENT               => 'Wysłane',
            self::PAID               => 'Opłacone',
            self::PARTIALLY_PAID     => 'Częściowo Opłacone',
            self::OVERDUE            => 'Przeterminowane',
            self::CANCELLED          => 'Anulowane',
        };
    }

    public function requiresAllocation(): bool
    {
        return match ($this) {
            self::OCR_COMPLETED, self::PENDING_ALLOCATION => true,
            default => false,
        };
    }

    public function isInApprovalProcess(): bool
    {
        return match ($this) {
            self::PENDING_APPROVAL => true,
            default                => false,
        };
    }

    public function isCompleted(): bool
    {
        return match ($this) {
            self::APPROVED, self::PAID, self::PARTIALLY_PAID, self::CANCELLED => true,
            default => false,
        };
    }

    public function canTransitionTo(InvoiceStatus $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newStatus, [
                self::OCR_PROCESSING, self::PENDING_ALLOCATION, self::CANCELLED,
            ], true),
            self::OCR_PROCESSING => in_array($newStatus, [
                self::OCR_COMPLETED, self::OCR_FAILED, self::CANCELLED,
            ], true),
            self::OCR_COMPLETED => in_array($newStatus, [
                self::PENDING_ALLOCATION, self::ALLOCATED, self::CANCELLED,
            ], true),
            self::OCR_FAILED => in_array($newStatus, [
                self::OCR_PROCESSING, self::PENDING_ALLOCATION, self::CANCELLED,
            ], true),
            self::PENDING_ALLOCATION => in_array($newStatus, [
                self::ALLOCATED, self::CANCELLED,
            ], true),
            self::ALLOCATED => in_array($newStatus, [
                self::PENDING_APPROVAL, self::APPROVED, self::SENT, self::CANCELLED,
            ], true),
            self::PENDING_APPROVAL => in_array($newStatus, [
                self::APPROVED, self::APPROVAL_REJECTED, self::CANCELLED,
            ], true),
            self::APPROVAL_REJECTED => in_array($newStatus, [
                self::PENDING_ALLOCATION, self::ALLOCATED, self::CANCELLED,
            ], true),
            self::APPROVED => in_array($newStatus, [
                self::SENT, self::PAID, self::CANCELLED,
            ], true),
            self::SENT => in_array($newStatus, [
                self::PAID, self::PARTIALLY_PAID, self::OVERDUE, self::CANCELLED,
            ], true),
            self::PAID, self::CANCELLED => false, // Final states
            self::PARTIALLY_PAID => in_array($newStatus, [
                self::PAID, self::OVERDUE, self::CANCELLED,
            ], true),
            self::OVERDUE => in_array($newStatus, [
                self::PAID, self::PARTIALLY_PAID, self::CANCELLED,
            ], true),
        };
    }
}
