<?php

namespace App\Domain\Approval\Actions;

use App\Domain\Approval\Models\ApprovalExpenseExecution;
use App\Domain\Approval\Services\WorkflowMatchingService;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use Illuminate\Support\Facades\Log;

class StartApprovalWorkflowAction
{
    public function __construct(
        private WorkflowMatchingService $workflowMatcher
    ) {
    }

    /**
     * Start approval workflow for an expense.
     * Returns the execution if a workflow was found, null if auto-approved.
     */
    public function execute(Expense $expense, ?string $initiatorId = null): ?ApprovalExpenseExecution
    {
        Log::info('Starting approval workflow for expense', [
            'expense_id'   => $expense->id,
            'tenant_id'    => $expense->tenant_id,
            'amount'       => $expense->total_gross->toFloat(),
            'initiator_id' => $initiatorId,
        ]);

        // Check if expense already has an active approval execution
        /** @var ?ApprovalExpenseExecution $existingExecution */
        $existingExecution = $expense->approvalExecution()->pending()->first(); // @phpstan-ignore-line

        if ($existingExecution) {
            Log::warning('Expense already has active approval execution', [
                'expense_id'    => $expense->id,
                'execution_id'  => $existingExecution->id,
            ]);

            return $existingExecution;
        }

        // Find matching workflow
        $workflow = $this->workflowMatcher->findMatchingWorkflow($expense);

        if (!$workflow) {
            // No workflow matches - auto-approve
            Log::info('No workflow matched - auto-approving expense', [
                'expense_id' => $expense->id,
            ]);

            $expense->update(['approval_status' => ApprovalStatus::APPROVED]);

            return null;
        }

        // Validate workflow has steps
        if (!$workflow->hasSteps()) {
            Log::warning('Workflow has no steps - auto-approving expense', [
                'expense_id'  => $expense->id,
                'workflow_id' => $workflow->id,
            ]);

            $expense->update(['approval_status' => ApprovalStatus::APPROVED]);

            return null;
        }

        // Create approval execution
        $firstStep = $workflow->getFirstStep();

        $execution = ApprovalExpenseExecution::create([
            'expense_id'       => $expense->id,
            'workflow_id'      => $workflow->id,
            'current_step_id'  => $firstStep->id,
            'initiator_id'     => $initiatorId,
            'started_at'       => now(),
        ]);

        // Update expense status to pending approval
        $expense->update(['approval_status' => ApprovalStatus::PENDING]);

        Log::info('Approval workflow started', [
            'expense_id'    => $expense->id,
            'execution_id'  => $execution->id,
            'workflow_id'   => $workflow->id,
            'workflow_name' => $workflow->name,
            'first_step_id' => $firstStep->id,
        ]);

        return $execution;
    }

    /**
     * Check if an expense can start an approval workflow.
     */
    public function canStartApproval(Expense $expense): bool
    {
        // Can start approval if:
        // 1. Expense is in a state that allows approval (e.g., allocated or processing)
        // 2. No active approval execution exists
        return in_array($expense->status, [
            InvoiceStatus::PROCESSING,
        ]) && !$expense->approvalExecution()->pending()->exists(); // @phpstan-ignore-line
    }

    /**
     * Get the reason why approval cannot be started (for debugging).
     */
    public function getCannotStartReason(Expense $expense): ?string
    {
        if (!in_array($expense->status, [InvoiceStatus::PROCESSING])) {
            return "Expense status '{$expense->status->value}' does not allow starting approval";
        }

        if ($expense->approvalExecution()->pending()->exists()) { // @phpstan-ignore-line
            return 'Expense already has an active approval execution';
        }

        return null;
    }
}
