<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\DeliveryStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\PaymentStatus;

/**
 * Comprehensive status tracking for invoices/expenses.
 *
 * @property InvoiceStatus $general - Overall workflow status
 * @property OcrRequestStatus $ocr - OCR processing status
 * @property AllocationStatus $allocation - Cost allocation status
 * @property ApprovalStatus $approval - Approval workflow status
 * @property DeliveryStatus $delivery - Sending/delivery status
 * @property PaymentStatus $payment - Payment status
 */
final class InvoiceStatusDTO extends BaseDataDTO
{
    public function __construct(
        public InvoiceStatus $general,
        public OcrRequestStatus $ocr,
        public AllocationStatus $allocation,
        public ApprovalStatus $approval,
        public DeliveryStatus $delivery,
        public PaymentStatus $payment,
    ) {}

    public function toArray(): array
    {
        return [
            'general' => $this->general->value,
            'ocr' => $this->ocr->value,
            'allocation' => $this->allocation->value,
            'approval' => $this->approval->value,
            'delivery' => $this->delivery->value,
            'payment' => $this->payment->value,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            general: InvoiceStatus::from($data['general']),
            ocr: OcrRequestStatus::from($data['ocr']),
            allocation: AllocationStatus::from($data['allocation']),
            approval: ApprovalStatus::from($data['approval']),
            delivery: DeliveryStatus::from($data['delivery']),
            payment: PaymentStatus::from($data['payment']),
        );
    }

    /**
     * Create default status for a new draft invoice.
     */
    public static function createDraft(): static
    {
        return new self(
            general: InvoiceStatus::DRAFT,
            ocr: OcrRequestStatus::Pending,
            allocation: AllocationStatus::NOT_REQUIRED,
            approval: ApprovalStatus::NOT_REQUIRED,
            delivery: DeliveryStatus::NOT_SENT,
            payment: PaymentStatus::PENDING,
        );
    }

    /**
     * Get a human-readable overall status description.
     */
    public function getOverallDescription(): string
    {
        // Prioritize showing blocking issues
        if ($this->ocr === OcrRequestStatus::Failed) {
            return 'OCR Processing Failed';
        }

        if ($this->approval === ApprovalStatus::REJECTED) {
            return 'Approval Rejected';
        }

        if ($this->delivery === DeliveryStatus::FAILED) {
            return 'Delivery Failed';
        }

        // Show current active process
        if ($this->ocr === OcrRequestStatus::Processing) {
            return 'Processing OCR';
        }

        if ($this->allocation->requiresAction()) {
            return 'Awaiting Allocation';
        }

        if ($this->approval === ApprovalStatus::PENDING) {
            return 'Awaiting Approval';
        }

        if ($this->delivery === DeliveryStatus::PENDING) {
            return 'Preparing for Delivery';
        }

        if ($this->delivery->isCompleted() && $this->payment->requiresAction()) {
            if ($this->payment === PaymentStatus::OVERDUE) {
                return 'Payment Overdue';
            }

            return 'Awaiting Payment';
        }

        if ($this->payment === PaymentStatus::PAID) {
            return 'Completed';
        }

        return $this->general->label();
    }

    /**
     * Check if the invoice needs immediate attention.
     */
    public function needsAttention(): bool
    {
        return $this->ocr === OcrRequestStatus::Failed
            || $this->approval === ApprovalStatus::REJECTED
            || $this->delivery === DeliveryStatus::FAILED
            || $this->payment === PaymentStatus::OVERDUE;
    }

    /**
     * Check if the invoice is ready for the next stage.
     */
    public function isReadyForNextStage(): bool
    {
        return match ($this->general) {
            InvoiceStatus::DRAFT => $this->ocr === OcrRequestStatus::Completed,
            InvoiceStatus::PROCESSING => $this->isProcessingComplete(),
            InvoiceStatus::ISSUED => true,
            InvoiceStatus::COMPLETED, InvoiceStatus::CANCELLED => false,
        };
    }

    /**
     * Check if all processing steps are complete.
     */
    private function isProcessingComplete(): bool
    {
        $ocrComplete = $this->ocr === OcrRequestStatus::Completed;
        $allocationComplete = $this->allocation === AllocationStatus::NOT_REQUIRED
            || $this->allocation === AllocationStatus::FULLY_ALLOCATED;
        $approvalComplete = $this->approval === ApprovalStatus::NOT_REQUIRED
            || $this->approval === ApprovalStatus::APPROVED;

        return $ocrComplete && $allocationComplete && $approvalComplete;
    }

    /**
     * Get all statuses that require action.
     */
    public function getActionableStatuses(): array
    {
        $actionable = [];

        if ($this->ocr === OcrRequestStatus::Failed) {
            $actionable[] = 'ocr';
        }

        if ($this->allocation->requiresAction()) {
            $actionable[] = 'allocation';
        }

        if ($this->approval === ApprovalStatus::PENDING) {
            $actionable[] = 'approval';
        }

        if ($this->approval === ApprovalStatus::REJECTED) {
            $actionable[] = 'approval';
        }

        if ($this->delivery === DeliveryStatus::FAILED) {
            $actionable[] = 'delivery';
        }

        if ($this->payment->requiresAction()) {
            $actionable[] = 'payment';
        }

        return $actionable;
    }
}
