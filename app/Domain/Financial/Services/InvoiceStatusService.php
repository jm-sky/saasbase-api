<?php

namespace App\Domain\Financial\Services;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Financial\DTOs\InvoiceStatusDTO;
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\DeliveryStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\PaymentStatus;

class InvoiceStatusService
{
    /**
     * Update the general status based on individual status components.
     */
    public function calculateGeneralStatus(InvoiceStatusDTO $status): InvoiceStatus
    {
        // If completed or cancelled, stay that way
        if ($status->general->isCompleted()) {
            return $status->general;
        }

        // If payment is complete, mark as completed
        if (PaymentStatus::PAID === $status->payment) {
            return InvoiceStatus::COMPLETED;
        }

        // If sent and has payment activity, mark as active
        if ($status->delivery->isCompleted() && PaymentStatus::PENDING !== $status->payment) {
            return InvoiceStatus::ACTIVE;
        }

        // If all processing is complete and ready to send
        if ($this->isReadyToSend($status)) {
            return InvoiceStatus::READY;
        }

        // If any processing is happening or needed
        if ($this->isProcessingNeeded($status)) {
            return InvoiceStatus::PROCESSING;
        }

        // Default to current status
        return $status->general;
    }

    /**
     * Handle OCR completion and update related statuses.
     */
    public function handleOcrCompletion(InvoiceStatusDTO $status): InvoiceStatusDTO
    {
        if (OcrRequestStatus::Completed !== $status->ocr) {
            return $status;
        }

        $newStatus = clone $status;

        // If allocation is required, set it to pending
        if (AllocationStatus::NOT_REQUIRED === $newStatus->allocation) {
            // This would be determined by business rules
            $newStatus->allocation = AllocationStatus::PENDING;
        }

        // Update general status
        $newStatus->general = $this->calculateGeneralStatus($newStatus);

        return $newStatus;
    }

    /**
     * Handle allocation completion.
     */
    public function handleAllocationCompletion(InvoiceStatusDTO $status): InvoiceStatusDTO
    {
        if (AllocationStatus::FULLY_ALLOCATED !== $status->allocation) {
            return $status;
        }

        $newStatus = clone $status;

        // If approval is required, set it to pending
        if (ApprovalStatus::NOT_REQUIRED === $newStatus->approval) {
            // This would be determined by business rules (amount, type, etc.)
            $newStatus->approval = ApprovalStatus::PENDING;
        }

        // Update general status
        $newStatus->general = $this->calculateGeneralStatus($newStatus);

        return $newStatus;
    }

    /**
     * Handle approval completion.
     */
    public function handleApprovalCompletion(InvoiceStatusDTO $status): InvoiceStatusDTO
    {
        if (ApprovalStatus::APPROVED !== $status->approval) {
            return $status;
        }

        $newStatus          = clone $status;
        $newStatus->general = $this->calculateGeneralStatus($newStatus);

        return $newStatus;
    }

    /**
     * Handle delivery/sending.
     */
    public function handleDeliveryStatusChange(InvoiceStatusDTO $status, DeliveryStatus $newDeliveryStatus): InvoiceStatusDTO
    {
        $newStatus           = clone $status;
        $newStatus->delivery = $newDeliveryStatus;

        // Update general status based on delivery
        $newStatus->general = $this->calculateGeneralStatus($newStatus);

        return $newStatus;
    }

    /**
     * Handle payment status changes.
     */
    public function handlePaymentStatusChange(InvoiceStatusDTO $status, PaymentStatus $newPaymentStatus): InvoiceStatusDTO
    {
        $newStatus          = clone $status;
        $newStatus->payment = $newPaymentStatus;

        // Update general status based on payment
        $newStatus->general = $this->calculateGeneralStatus($newStatus);

        return $newStatus;
    }

    /**
     * Check if invoice is ready to be sent.
     */
    private function isReadyToSend(InvoiceStatusDTO $status): bool
    {
        $ocrComplete = OcrRequestStatus::Completed === $status->ocr;

        $allocationComplete = AllocationStatus::NOT_REQUIRED === $status->allocation
            || AllocationStatus::FULLY_ALLOCATED === $status->allocation;

        $approvalComplete = ApprovalStatus::NOT_REQUIRED === $status->approval
            || ApprovalStatus::APPROVED === $status->approval;

        $notYetSent = DeliveryStatus::NOT_SENT === $status->delivery;

        return $ocrComplete && $allocationComplete && $approvalComplete && $notYetSent;
    }

    /**
     * Check if any processing is needed or happening.
     */
    private function isProcessingNeeded(InvoiceStatusDTO $status): bool
    {
        // OCR processing
        if (in_array($status->ocr, [OcrRequestStatus::Pending, OcrRequestStatus::Processing])) {
            return true;
        }

        // Allocation needed
        if ($status->allocation->requiresAction()) {
            return true;
        }

        // Approval pending
        if (ApprovalStatus::PENDING === $status->approval) {
            return true;
        }

        // Failed states that need reprocessing
        if (OcrRequestStatus::Failed === $status->ocr) {
            return true;
        }

        if (ApprovalStatus::REJECTED === $status->approval) {
            return true;
        }

        return false;
    }

    /**
     * Get recommended next actions for an invoice.
     */
    public function getRecommendedActions(InvoiceStatusDTO $status): array
    {
        $actions = [];

        if (OcrRequestStatus::Failed === $status->ocr) {
            $actions[] = 'Retry OCR processing';
        }

        if ($status->allocation->requiresAction()) {
            $actions[] = 'Complete cost allocation';
        }

        if (ApprovalStatus::PENDING === $status->approval) {
            $actions[] = 'Pending approval decision';
        }

        if (ApprovalStatus::REJECTED === $status->approval) {
            $actions[] = 'Address approval rejection';
        }

        if ($status->isReadyForNextStage() && InvoiceStatus::READY === $status->general) {
            $actions[] = 'Ready to send';
        }

        if (DeliveryStatus::FAILED === $status->delivery) {
            $actions[] = 'Retry delivery';
        }

        if (PaymentStatus::OVERDUE === $status->payment) {
            $actions[] = 'Follow up on overdue payment';
        }

        return $actions;
    }
}
