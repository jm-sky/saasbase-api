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
 * @property InvoiceStatus    $general    - Overall workflow status
 * @property OcrRequestStatus $ocr        - OCR processing status
 * @property AllocationStatus $allocation - Cost allocation status
 * @property ApprovalStatus   $approval   - Approval workflow status
 * @property DeliveryStatus   $delivery   - Sending/delivery status
 * @property PaymentStatus    $payment    - Payment status
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
    ) {
    }

    public function toArray(): array
    {
        return [
            'general'    => $this->general->value,
            'ocr'        => $this->ocr->value,
            'allocation' => $this->allocation->value,
            'approval'   => $this->approval->value,
            'delivery'   => $this->delivery->value,
            'payment'    => $this->payment->value,
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
        if (OcrRequestStatus::Failed === $this->ocr) {
            return 'OCR Processing Failed';
        }

        if (ApprovalStatus::REJECTED === $this->approval) {
            return 'Approval Rejected';
        }

        if (DeliveryStatus::FAILED === $this->delivery) {
            return 'Delivery Failed';
        }

        // Show current active process
        if (OcrRequestStatus::Processing === $this->ocr) {
            return 'Processing OCR';
        }

        if ($this->allocation->requiresAction()) {
            return 'Awaiting Allocation';
        }

        if (ApprovalStatus::PENDING === $this->approval) {
            return 'Awaiting Approval';
        }

        if (DeliveryStatus::PENDING === $this->delivery) {
            return 'Preparing for Delivery';
        }

        if ($this->delivery->isCompleted() && $this->payment->requiresAction()) {
            if (PaymentStatus::OVERDUE === $this->payment) {
                return 'Payment Overdue';
            }

            return 'Awaiting Payment';
        }

        if (PaymentStatus::PAID === $this->payment) {
            return 'Completed';
        }

        return $this->general->label();
    }

    /**
     * Check if the invoice needs immediate attention.
     */
    public function needsAttention(): bool
    {
        return OcrRequestStatus::Failed === $this->ocr
            || ApprovalStatus::REJECTED === $this->approval
            || DeliveryStatus::FAILED === $this->delivery
            || PaymentStatus::OVERDUE === $this->payment;
    }

    /**
     * Check if the invoice is ready for the next stage.
     */
    public function isReadyForNextStage(): bool
    {
        return match ($this->general) {
            InvoiceStatus::DRAFT      => OcrRequestStatus::Completed === $this->ocr,
            InvoiceStatus::PROCESSING => $this->isProcessingComplete(),
            InvoiceStatus::ISSUED     => true,
            InvoiceStatus::COMPLETED, InvoiceStatus::CANCELLED => false,
        };
    }

    /**
     * Check if all processing steps are complete.
     */
    private function isProcessingComplete(): bool
    {
        $ocrComplete        = OcrRequestStatus::Completed === $this->ocr;
        $allocationComplete = AllocationStatus::NOT_REQUIRED === $this->allocation
            || AllocationStatus::FULLY_ALLOCATED === $this->allocation;
        $approvalComplete = ApprovalStatus::NOT_REQUIRED === $this->approval
            || ApprovalStatus::APPROVED === $this->approval;

        return $ocrComplete && $allocationComplete && $approvalComplete;
    }

    /**
     * Get all statuses that require action.
     */
    public function getActionableStatuses(): array
    {
        $actionable = [];

        if (OcrRequestStatus::Failed === $this->ocr) {
            $actionable[] = 'ocr';
        }

        if ($this->allocation->requiresAction()) {
            $actionable[] = 'allocation';
        }

        if (ApprovalStatus::PENDING === $this->approval) {
            $actionable[] = 'approval';
        }

        if (ApprovalStatus::REJECTED === $this->approval) {
            $actionable[] = 'approval';
        }

        if (DeliveryStatus::FAILED === $this->delivery) {
            $actionable[] = 'delivery';
        }

        if ($this->payment->requiresAction()) {
            $actionable[] = 'payment';
        }

        return $actionable;
    }
}
