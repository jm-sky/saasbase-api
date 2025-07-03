<?php

namespace App\Domain\Financial\Examples;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Financial\DTOs\InvoiceStatusDTO;
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\DeliveryStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Domain\Financial\Services\InvoiceStatusService;

/**
 * Example demonstrating the new status architecture.
 *
 * This solves the original problem where an invoice couldn't be both
 * "Sent" and "Overdue" at the same time with a single status field.
 */
class StatusArchitectureExample
{
    public function demonstrateStatusCombinations()
    {
        $statusService = new InvoiceStatusService();

        // Example 1: Invoice that is SENT and OVERDUE simultaneously
        $sentAndOverdue = new InvoiceStatusDTO(
            general: InvoiceStatus::ISSUED,          // General state: active in the system
            ocr: OcrRequestStatus::Completed,       // OCR: completed
            allocation: AllocationStatus::FULLY_ALLOCATED, // Allocation: complete
            approval: ApprovalStatus::APPROVED,     // Approval: approved
            delivery: DeliveryStatus::SENT,         // Delivery: SENT ✓
            payment: PaymentStatus::OVERDUE         // Payment: OVERDUE ✓
        );

        echo "=== Example 1: Invoice both SENT and OVERDUE ===\n";
        echo 'Overall Description: ' . $sentAndOverdue->getOverallDescription() . "\n";
        echo 'Delivery Status: ' . $sentAndOverdue->delivery->label() . "\n";
        echo 'Payment Status: ' . $sentAndOverdue->payment->label() . "\n";
        echo 'Needs Attention: ' . ($sentAndOverdue->needsAttention() ? 'YES' : 'NO') . "\n";
        echo 'Recommended Actions: ' . implode(', ', $statusService->getRecommendedActions($sentAndOverdue)) . "\n\n";

        // Example 2: Invoice with complex processing state
        $complexProcessing = new InvoiceStatusDTO(
            general: InvoiceStatus::PROCESSING,
            ocr: OcrRequestStatus::Completed,
            allocation: AllocationStatus::PARTIALLY_ALLOCATED, // Needs more allocation
            approval: ApprovalStatus::PENDING,      // Waiting for approval
            delivery: DeliveryStatus::NOT_SENT,
            payment: PaymentStatus::PENDING
        );

        echo "=== Example 2: Complex Processing State ===\n";
        echo 'Overall Description: ' . $complexProcessing->getOverallDescription() . "\n";
        echo 'Actionable Statuses: ' . implode(', ', $complexProcessing->getActionableStatuses()) . "\n";
        echo 'Ready for Next Stage: ' . ($complexProcessing->isReadyForNextStage() ? 'YES' : 'NO') . "\n";
        echo 'Recommended Actions: ' . implode(', ', $statusService->getRecommendedActions($complexProcessing)) . "\n\n";

        // Example 3: Failed states requiring attention
        $failedState = new InvoiceStatusDTO(
            general: InvoiceStatus::PROCESSING,
            ocr: OcrRequestStatus::Failed,          // OCR failed
            allocation: AllocationStatus::NOT_REQUIRED,
            approval: ApprovalStatus::REJECTED,     // Approval was rejected
            delivery: DeliveryStatus::FAILED,       // Delivery failed
            payment: PaymentStatus::PENDING
        );

        echo "=== Example 3: Multiple Failed States ===\n";
        echo 'Overall Description: ' . $failedState->getOverallDescription() . "\n";
        echo 'Needs Attention: ' . ($failedState->needsAttention() ? 'YES' : 'NO') . "\n";
        echo 'Actionable Statuses: ' . implode(', ', $failedState->getActionableStatuses()) . "\n";
        echo 'Recommended Actions: ' . implode(', ', $statusService->getRecommendedActions($failedState)) . "\n\n";

        // Example 4: Workflow progression
        echo "=== Example 4: Status Workflow Progression ===\n";
        $this->demonstrateWorkflowProgression($statusService);
    }

    private function demonstrateWorkflowProgression(InvoiceStatusService $statusService)
    {
        // Start with a new draft invoice
        $status = InvoiceStatusDTO::createDraft();
        echo "1. Draft Invoice:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Description: {$status->getOverallDescription()}\n\n";

        // OCR completes
        $status->ocr = OcrRequestStatus::Completed;
        $status      = $statusService->handleOcrCompletion($status);
        echo "2. After OCR Completion:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Allocation: {$status->allocation->label()}\n";
        echo "   Description: {$status->getOverallDescription()}\n\n";

        // Allocation completes
        $status->allocation = AllocationStatus::FULLY_ALLOCATED;
        $status             = $statusService->handleAllocationCompletion($status);
        echo "3. After Allocation Completion:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Approval: {$status->approval->label()}\n";
        echo "   Description: {$status->getOverallDescription()}\n\n";

        // Approval completes
        $status->approval = ApprovalStatus::APPROVED;
        $status           = $statusService->handleApprovalCompletion($status);
        echo "4. After Approval:\n";
        echo "   General: {$status->general->label()}\n";
        echo '   Ready to Send: ' . ($status->general->canBeSent() ? 'YES' : 'NO') . "\n";
        echo "   Description: {$status->getOverallDescription()}\n\n";

        // Invoice gets sent
        $status = $statusService->handleDeliveryStatusChange($status, DeliveryStatus::SENT);
        echo "5. After Sending:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Delivery: {$status->delivery->label()}\n";
        echo "   Description: {$status->getOverallDescription()}\n\n";

        // Payment becomes overdue (this is the key example!)
        $status = $statusService->handlePaymentStatusChange($status, PaymentStatus::OVERDUE);
        echo "6. When Payment Becomes Overdue:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Delivery: {$status->delivery->label()} (still SENT)\n";
        echo "   Payment: {$status->payment->label()} (now OVERDUE)\n";
        echo "   Description: {$status->getOverallDescription()}\n";
        echo "   🎯 SOLUTION: Invoice is both SENT and OVERDUE simultaneously!\n\n";

        // Partial payment received
        $status = $statusService->handlePaymentStatusChange($status, PaymentStatus::PARTIALLY_PAID);
        echo "7. After Partial Payment:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Delivery: {$status->delivery->label()} (still SENT)\n";
        echo "   Payment: {$status->payment->label()} (now PARTIALLY_PAID)\n";
        echo "   Description: {$status->getOverallDescription()}\n\n";

        // Full payment received
        $status = $statusService->handlePaymentStatusChange($status, PaymentStatus::PAID);
        echo "8. After Full Payment:\n";
        echo "   General: {$status->general->label()}\n";
        echo "   Delivery: {$status->delivery->label()}\n";
        echo "   Payment: {$status->payment->label()}\n";
        echo "   Description: {$status->getOverallDescription()}\n";
        echo "   ✅ Invoice lifecycle complete!\n";
    }

    public function demonstrateStatusQueries()
    {
        echo "\n=== Status Query Examples ===\n";

        // Example: Find all invoices that are sent but not paid
        $sentButNotPaid = [
            'delivery_status' => DeliveryStatus::SENT,
            'payment_status'  => [PaymentStatus::PENDING, PaymentStatus::OVERDUE, PaymentStatus::PARTIALLY_PAID],
        ];

        echo "Query: Invoices that are SENT but not fully PAID\n";
        echo "Conditions: delivery_status = 'sent' AND payment_status IN ('pending', 'overdue', 'partiallyPaid')\n\n";

        // Example: Find invoices needing immediate attention
        $needsAttention = [
            'OR' => [
                ['payment_status' => PaymentStatus::OVERDUE],
                ['ocr_status'      => OcrRequestStatus::Failed],
                ['approval_status' => ApprovalStatus::REJECTED],
                ['delivery_status' => DeliveryStatus::FAILED],
            ],
        ];

        echo "Query: Invoices needing immediate attention\n";
        echo "Conditions: payment_status = 'overdue' OR ocr_status = 'failed' OR approval_status = 'rejected' OR delivery_status = 'failed'\n\n";

        // Example: Find invoices ready for next workflow step
        $readyForProcessing = [
            'general_status'    => InvoiceStatus::PROCESSING,
            'ocr_status'        => OcrRequestStatus::Completed,
            'allocation_status' => AllocationStatus::PENDING,
        ];

        echo "Query: Invoices ready for allocation\n";
        echo "Conditions: general_status = 'processing' AND ocr_status = 'completed' AND allocation_status = 'pending'\n";
    }
}

// Usage example:
// $example = new StatusArchitectureExample();
// $example->demonstrateStatusCombinations();
// $example->demonstrateStatusQueries();
