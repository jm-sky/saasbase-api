<?php

namespace App\Domain\Approval\Actions;

use App\Domain\Approval\Enums\ApprovalDecision;
use App\Domain\Approval\Enums\ApprovalExecutionStatus;
use App\Domain\Approval\Models\ApprovalExpenseDecision;
use App\Domain\Approval\Models\ApprovalExpenseExecution;
use App\Domain\Approval\Services\ApprovalResolutionService;
use App\Domain\Auth\Models\User;
use App\Domain\Financial\Enums\ApprovalStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessApprovalDecisionAction
{
    public function __construct(
        private ApprovalResolutionService $approvalResolver
    ) {
    }

    /**
     * Process an approval decision for an expense execution.
     */
    public function execute(
        ApprovalExpenseExecution $execution,
        User $approver,
        ApprovalDecision $decision,
        ?string $reason = null
    ): ApprovalExpenseDecision {
        Log::info('Processing approval decision', [
            'execution_id' => $execution->id,
            'expense_id'   => $execution->expense_id,
            'approver_id'  => $approver->id,
            'decision'     => $decision->value,
            'step_id'      => $execution->current_step_id,
        ]);

        // Check if approver already made a decision for this step first
        $existingDecision = $this->getExistingDecision($execution, $approver);

        if ($existingDecision) {
            Log::warning('Approver already made decision for this step', [
                'execution_id'         => $execution->id,
                'approver_id'          => $approver->id,
                'step_id'              => $execution->current_step_id,
                'existing_decision_id' => $existingDecision->id,
            ]);

            return $existingDecision;
        }

        // Validate execution can receive decisions
        $this->validateExecution($execution);

        // Validate approver can make decision for current step
        $this->validateApprover($execution, $approver);

        return DB::transaction(function () use ($execution, $approver, $decision, $reason) {
            // Record the decision
            $decisionRecord = $this->recordDecision($execution, $approver, $decision, $reason);

            // Check if current step is complete
            if ($this->isStepComplete($execution)) {
                // Check if this was a rejection
                if ($this->hasRejection($execution)) {
                    $this->rejectWorkflow($execution);
                } else {
                    // Step approved, check if workflow is complete
                    if ($this->isWorkflowComplete($execution)) {
                        $this->approveWorkflow($execution);
                    } else {
                        $this->progressToNextStep($execution);
                    }
                }
            }

            return $decisionRecord;
        });
    }

    /**
     * Validate that the execution can receive decisions.
     */
    private function validateExecution(ApprovalExpenseExecution $execution): void
    {
        if (!$execution->isPending()) {
            throw new \InvalidArgumentException("Execution is not pending (status: {$execution->status->value})");
        }

        if (!$execution->current_step_id) {
            throw new \InvalidArgumentException('Execution has no current step');
        }
    }

    /**
     * Validate that the approver can make a decision for the current step.
     */
    private function validateApprover(ApprovalExpenseExecution $execution, User $approver): void
    {
        $currentStep = $execution->currentStep;

        if (!$currentStep) {
            throw new \InvalidArgumentException('Current step not found');
        }

        // Check if approver is valid for any of the step's approver configurations
        $isValidApprover = false;

        // Load expense relationship if not already loaded
        $execution->loadMissing('expense');

        foreach ($currentStep->approvers as $stepApprover) {
            $validApprovers = $this->approvalResolver->resolveApprovers($stepApprover, $execution->expense);

            if ($validApprovers->contains('id', $approver->id)) {
                $isValidApprover = true;
                break;
            }
        }

        if (!$isValidApprover) {
            throw new \InvalidArgumentException('User is not authorized to approve this step');
        }
    }

    /**
     * Check if approver already made a decision for current step.
     */
    private function getExistingDecision(ApprovalExpenseExecution $execution, User $approver): ?ApprovalExpenseDecision
    {
        // @phpstan-ignore-next-line
        return $execution->decisions()
            ->where('step_id', $execution->current_step_id)
            ->where('approver_id', $approver->id)
            ->first()
        ;
    }

    /**
     * Record the approval decision.
     */
    private function recordDecision(
        ApprovalExpenseExecution $execution,
        User $approver,
        ApprovalDecision $decision,
        ?string $reason
    ): ApprovalExpenseDecision {
        return ApprovalExpenseDecision::create([
            'execution_id' => $execution->id,
            'step_id'      => $execution->current_step_id,
            'approver_id'  => $approver->id,
            'decision'     => $decision,
            'reason'       => $reason,
            'decided_at'   => now(),
        ]);
    }

    /**
     * Check if the current step is complete.
     */
    private function isStepComplete(ApprovalExpenseExecution $execution): bool
    {
        $currentStep = $execution->currentStep;

        if (!$currentStep) {
            return false;
        }

        // Get all decisions for current step
        $stepDecisions = $execution->decisions()
            ->where('step_id', $execution->current_step_id)
            ->get()
        ;

        // Check for any rejections
        $rejections = $stepDecisions->where('decision', ApprovalDecision::REJECTED);

        if ($rejections->isNotEmpty()) {
            // Any rejection completes the step (and workflow)
            return true;
        }

        // Count approvals
        $approvals     = $stepDecisions->where('decision', ApprovalDecision::APPROVED);
        $approvalCount = $approvals->count();

        // Check completion based on step requirements
        if ($currentStep->require_all_approvers) {
            // Need all configured approvers to approve
            $totalApprovers = $this->getTotalApproversForStep($execution, $currentStep);

            return $approvalCount >= $totalApprovers;
        }

        // Need minimum number of approvers
        return $approvalCount >= $currentStep->min_approvers;
    }

    /**
     * Get total number of approvers for a step.
     */
    private function getTotalApproversForStep(ApprovalExpenseExecution $execution, $step): int
    {
        // Load expense relationship if not already loaded
        $execution->loadMissing('expense');

        $totalApprovers = 0;

        foreach ($step->approvers as $stepApprover) {
            $approvers = $this->approvalResolver->resolveApprovers($stepApprover, $execution->expense);
            $totalApprovers += $approvers->count();
        }

        return $totalApprovers;
    }

    /**
     * Check if current step has any rejections.
     */
    private function hasRejection(ApprovalExpenseExecution $execution): bool
    {
        return $execution->decisions()
            ->where('step_id', $execution->current_step_id)
            ->where('decision', ApprovalDecision::REJECTED)
            ->exists()
        ;
    }

    /**
     * Check if the entire workflow is complete.
     */
    private function isWorkflowComplete(ApprovalExpenseExecution $execution): bool
    {
        $currentStep = $execution->currentStep;

        if (!$currentStep) {
            return true;
        }

        // Check if this is the last step
        return $currentStep->isLastStep();
    }

    /**
     * Progress to the next step in the workflow.
     */
    private function progressToNextStep(ApprovalExpenseExecution $execution): void
    {
        $currentStep = $execution->currentStep;

        if (!$currentStep) {
            return;
        }

        $nextStep = $currentStep->getNextStep();

        if (!$nextStep) {
            // No next step, complete workflow
            $this->approveWorkflow($execution);

            return;
        }

        $execution->update([
            'current_step_id' => $nextStep->id,
        ]);

        Log::info('Progressed to next step', [
            'execution_id'     => $execution->id,
            'expense_id'       => $execution->expense_id,
            'previous_step_id' => $currentStep->id,
            'next_step_id'     => $nextStep->id,
        ]);
    }

    /**
     * Approve the workflow and complete the execution.
     */
    private function approveWorkflow(ApprovalExpenseExecution $execution): void
    {
        $execution->update([
            'status'       => ApprovalExecutionStatus::APPROVED,
            'completed_at' => now(),
        ]);

        // Update expense approval status
        $execution->expense->update([
            'approval_status' => ApprovalStatus::APPROVED,
        ]);

        Log::info('Workflow approved', [
            'execution_id' => $execution->id,
            'expense_id'   => $execution->expense_id,
        ]);
    }

    /**
     * Reject the workflow and complete the execution.
     */
    private function rejectWorkflow(ApprovalExpenseExecution $execution): void
    {
        $execution->update([
            'status'       => ApprovalExecutionStatus::REJECTED,
            'completed_at' => now(),
        ]);

        // Update expense approval status
        $execution->expense->update([
            'approval_status' => ApprovalStatus::REJECTED,
        ]);

        Log::info('Workflow rejected', [
            'execution_id' => $execution->id,
            'expense_id'   => $execution->expense_id,
        ]);
    }

    /**
     * Check if a user can make a decision for a specific execution.
     */
    public function canUserMakeDecision(ApprovalExpenseExecution $execution, User $user): bool
    {
        if (!$execution->isPending()) {
            return false;
        }

        if (!$execution->current_step_id) {
            return false;
        }

        // Check if user already made a decision for this step
        if ($this->getExistingDecision($execution, $user)) {
            return false;
        }

        // Check if user is authorized for current step
        try {
            $this->validateApprover($execution, $user);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Get the reason why a user cannot make a decision (for debugging).
     */
    public function getCannotDecideReason(ApprovalExpenseExecution $execution, User $user): ?string
    {
        if (!$execution->isPending()) {
            return "Execution is not pending (status: {$execution->status->value})";
        }

        if (!$execution->current_step_id) {
            return 'Execution has no current step';
        }

        if ($this->getExistingDecision($execution, $user)) {
            return 'User already made a decision for this step';
        }

        try {
            $this->validateApprover($execution, $user);
        } catch (\InvalidArgumentException $e) {
            return $e->getMessage();
        }

        return null;
    }
}
