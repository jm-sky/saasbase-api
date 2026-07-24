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
        if ($status->payment === PaymentStatus::PAID) {
            return InvoiceStatus::COMPLETED;
        }

        // If sent and has payment activity, mark as active
        if ($status->delivery->isCompleted() && $status->payment !== PaymentStatus::PENDING) {
            return InvoiceStatus::ISSUED;
        }

        // If all processing is complete and ready to send
        if ($this->isReadyToSend($status)) {
            return InvoiceStatus::ISSUED;
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
        if ($status->ocr !== OcrRequestStatus::Completed) {
            return $status;
        }

        $newStatus = clone $status;

        // If allocation is required, set it to pending
        if ($newStatus->allocation === AllocationStatus::NOT_REQUIRED) {
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
        if ($status->allocation !== AllocationStatus::FULLY_ALLOCATED) {
            return $status;
        }

        $newStatus = clone $status;

        // If approval is required, set it to pending
        if ($newStatus->approval === ApprovalStatus::NOT_REQUIRED) {
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
        if ($status->approval !== ApprovalStatus::APPROVED) {
            return $status;
        }

        $newStatus = clone $status;
        $newStatus->general = $this->calculateGeneralStatus($newStatus);

        return $newStatus;
    }

    /**
     * Handle delivery/sending.
     */
    public function handleDeliveryStatusChange(InvoiceStatusDTO $status, DeliveryStatus $newDeliveryStatus): InvoiceStatusDTO
    {
        $newStatus = clone $status;
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
        $newStatus = clone $status;
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
        $ocrComplete = $status->ocr === OcrRequestStatus::Completed;

        $allocationComplete = $status->allocation === AllocationStatus::NOT_REQUIRED
            || $status->allocation === AllocationStatus::FULLY_ALLOCATED;

        $approvalComplete = $status->approval === ApprovalStatus::NOT_REQUIRED
            || $status->approval === ApprovalStatus::APPROVED;

        $notYetSent = $status->delivery === DeliveryStatus::NOT_SENT;

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
        if ($status->approval === ApprovalStatus::PENDING) {
            return true;
        }

        // Failed states that need reprocessing
        if ($status->ocr === OcrRequestStatus::Failed) {
            return true;
        }

        if ($status->approval === ApprovalStatus::REJECTED) {
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

        if ($status->ocr === OcrRequestStatus::Failed) {
            $actions[] = 'Retry OCR processing';
        }

        if ($status->allocation->requiresAction()) {
            $actions[] = 'Complete cost allocation';
        }

        if ($status->approval === ApprovalStatus::PENDING) {
            $actions[] = 'Pending approval decision';
        }

        if ($status->approval === ApprovalStatus::REJECTED) {
            $actions[] = 'Address approval rejection';
        }

        if ($status->isReadyForNextStage() && $status->general === InvoiceStatus::ISSUED) {
            $actions[] = 'Ready to send';
        }

        if ($status->delivery === DeliveryStatus::FAILED) {
            $actions[] = 'Retry delivery';
        }

        if ($status->payment === PaymentStatus::OVERDUE) {
            $actions[] = 'Follow up on overdue payment';
        }

        return $actions;
    }
}
