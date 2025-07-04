<?php

namespace Tests\Feature\Approval;

use App\Domain\Approval\Actions\ProcessApprovalDecisionAction;
use App\Domain\Approval\Actions\StartApprovalWorkflowAction;
use App\Domain\Approval\Enums\ApprovalDecision;
use App\Domain\Approval\Enums\ApprovalExecutionStatus;
use App\Domain\Approval\Enums\ApproverType;
use App\Domain\Approval\Models\ApprovalExpenseExecution;
use App\Domain\Approval\Models\ApprovalStepApprover;
use App\Domain\Approval\Models\ApprovalWorkflow;
use App\Domain\Approval\Models\ApprovalWorkflowStep;
use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ApprovalWorkflowExecutionTest extends TestCase
{
    use RefreshDatabase;

    private StartApprovalWorkflowAction $startAction;

    private ProcessApprovalDecisionAction $processAction;

    private Tenant $tenant;

    private User $expenseCreator;

    private User $approver1;

    private User $approver2;

    private User $approver3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startAction   = app(StartApprovalWorkflowAction::class);
        $this->processAction = app(ProcessApprovalDecisionAction::class);

        // Create test tenant with owner
        $owner        = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $owner->id]);

        // Initialize tenant with organizational structure
        (new InitializeTenantDefaults())->execute($this->tenant, $owner);

        // Create test users and attach them to tenant through organization system
        $this->expenseCreator = User::factory()->create();
        $this->tenant->attachUserToRootOrganizationUnit($this->expenseCreator, OrgUnitRole::Employee);

        $this->approver1 = User::factory()->create();
        $this->tenant->attachUserToRootOrganizationUnit($this->approver1, OrgUnitRole::DepartmentHead);

        $this->approver2 = User::factory()->create();
        $this->tenant->attachUserToRootOrganizationUnit($this->approver2, OrgUnitRole::TeamLead);

        $this->approver3 = User::factory()->create();
        $this->tenant->attachUserToRootOrganizationUnit($this->approver3, OrgUnitRole::CEO);
    }

    /** @test */
    public function itCanStartApprovalWorkflowForExpense(): void
    {
        // Given: An expense and matching workflow
        $expense  = $this->createExpense();
        $workflow = $this->createSingleStepWorkflow();

        // When: Starting approval workflow
        $execution = $this->startAction->execute($expense);

        // Then: Execution should be created
        $this->assertInstanceOf(ApprovalExpenseExecution::class, $execution);
        $this->assertEquals($expense->id, $execution->expense_id);
        $this->assertEquals($workflow->id, $execution->workflow_id);
        $this->assertEquals(ApprovalExecutionStatus::PENDING, $execution->status);
        $this->assertEquals(ApprovalStatus::PENDING, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itAutoApprovesWhenNoWorkflowMatches(): void
    {
        // Given: An expense with no matching workflow
        $expense = $this->createExpense();

        // When: Starting approval workflow
        $execution = $this->startAction->execute($expense);

        // Then: Should auto-approve
        $this->assertNull($execution);
        $this->assertEquals(ApprovalStatus::APPROVED, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itCanProcessApprovalDecisionAndCompleteSingleStepWorkflow(): void
    {
        // Given: A running approval execution
        $expense   = $this->createExpense();
        $workflow  = $this->createSingleStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // When: Processing approval decision
        $decision = $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);

        // Then: Decision should be recorded and workflow completed
        $this->assertNotNull($decision);
        $this->assertEquals(ApprovalDecision::APPROVED, $decision->decision);
        $this->assertEquals($this->approver1->id, $decision->approver_id);

        // Execution should be completed
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::APPROVED, $execution->status);
        $this->assertNotNull($execution->completed_at);

        // Expense should be approved
        $this->assertEquals(ApprovalStatus::APPROVED, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itCanProcessRejectionAndCompleteWorkflow(): void
    {
        // Given: A running approval execution
        $expense   = $this->createExpense();
        $workflow  = $this->createSingleStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // When: Processing rejection decision
        $decision = $this->processAction->execute($execution, $this->approver1, ApprovalDecision::REJECTED, 'Not valid');

        // Then: Decision should be recorded and workflow rejected
        $this->assertNotNull($decision);
        $this->assertEquals(ApprovalDecision::REJECTED, $decision->decision);
        $this->assertEquals('Not valid', $decision->notes);

        // Execution should be rejected
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::REJECTED, $execution->status);
        $this->assertNotNull($execution->completed_at);

        // Expense should be rejected
        $this->assertEquals(ApprovalStatus::REJECTED, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itCanHandleMultiStepWorkflow(): void
    {
        // Given: A multi-step workflow
        $expense   = $this->createExpense();
        $workflow  = $this->createMultiStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // When: First step is approved
        /** @var ApprovalWorkflowStep $step1 */
        $step1 = $workflow->steps()->where('step_order', 1)->first();
        $this->assertEquals($step1->id, $execution->current_step_id);

        $decision1 = $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);

        // Then: Should progress to next step
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::PENDING, $execution->status);

        /** @var ApprovalWorkflowStep $step2 */
        $step2 = $workflow->steps()->where('step_order', 2)->first();
        $this->assertEquals($step2->id, $execution->current_step_id);

        // When: Second step is approved
        $decision2 = $this->processAction->execute($execution, $this->approver2, ApprovalDecision::APPROVED);

        // Then: Workflow should be completed
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::APPROVED, $execution->status);
        $this->assertNotNull($execution->completed_at);
        $this->assertEquals(ApprovalStatus::APPROVED, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itCanHandleParallelApproversWithMinimumThreshold(): void
    {
        // Given: A workflow with parallel approvers (min 2 out of 3)
        $expense   = $this->createExpense();
        $workflow  = $this->createParallelApprovalWorkflow();
        $execution = $this->startAction->execute($expense);

        // When: First approver approves
        $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);

        // Then: Should still be pending
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::PENDING, $execution->status);

        // When: Second approver approves (reaches minimum threshold)
        $this->processAction->execute($execution, $this->approver2, ApprovalDecision::APPROVED);

        // Then: Should complete workflow
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::APPROVED, $execution->status);
        $this->assertEquals(ApprovalStatus::APPROVED, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itCanHandleRequireAllApproversWorkflow(): void
    {
        // Given: A workflow requiring all approvers
        $expense   = $this->createExpense();
        $workflow  = $this->createRequireAllApproversWorkflow();
        $execution = $this->startAction->execute($expense);

        // When: First approver approves
        $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);

        // Then: Should still be pending
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::PENDING, $execution->status);

        // When: Second approver approves (but not all yet)
        $this->processAction->execute($execution, $this->approver2, ApprovalDecision::APPROVED);

        // Then: Should still be pending
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::PENDING, $execution->status);

        // When: Third approver approves (all approvers)
        $this->processAction->execute($execution, $this->approver3, ApprovalDecision::APPROVED);

        // Then: Should complete workflow
        $execution->refresh();
        $this->assertEquals(ApprovalExecutionStatus::APPROVED, $execution->status);
        $this->assertEquals(ApprovalStatus::APPROVED, $expense->fresh()->approval_status);
    }

    /** @test */
    public function itPreventsDuplicateDecisionsFromSameApprover(): void
    {
        // Given: A running approval execution
        $expense   = $this->createExpense();
        $workflow  = $this->createSingleStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // When: Same approver makes decision twice
        $decision1 = $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);
        $decision2 = $this->processAction->execute($execution, $this->approver1, ApprovalDecision::REJECTED);

        // Then: Should return the same decision
        $this->assertEquals($decision1->id, $decision2->id);
        $this->assertEquals(ApprovalDecision::APPROVED, $decision2->decision);
    }

    /** @test */
    public function itValidatesApproverAuthorization(): void
    {
        // Given: A running approval execution
        $expense   = $this->createExpense();
        $workflow  = $this->createSingleStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // Create unauthorized user
        $unauthorizedUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // When: Unauthorized user tries to approve
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User is not authorized to approve this step');

        $this->processAction->execute($execution, $unauthorizedUser, ApprovalDecision::APPROVED);
    }

    /** @test */
    public function itProvidesDebugInformationForFailedApprovals(): void
    {
        // Given: A running approval execution
        $expense   = $this->createExpense();
        $workflow  = $this->createSingleStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // Create unauthorized user
        $unauthorizedUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // When: Checking if unauthorized user can approve
        $canApprove = $this->processAction->canUserMakeDecision($execution, $unauthorizedUser);
        $reason     = $this->processAction->getCannotDecideReason($execution, $unauthorizedUser);

        // Then: Should provide clear feedback
        $this->assertFalse($canApprove);
        $this->assertEquals('User is not authorized to approve this step', $reason);
    }

    /** @test */
    public function itPreventsApprovalOfCompletedExecutions(): void
    {
        // Given: A completed approval execution
        $expense   = $this->createExpense();
        $workflow  = $this->createSingleStepWorkflow();
        $execution = $this->startAction->execute($expense);

        // Complete the execution
        $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);

        // When: Trying to approve completed execution
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Execution is not pending');

        $this->processAction->execute($execution, $this->approver1, ApprovalDecision::APPROVED);
    }

    // Helper methods for creating test data

    private function createExpense(): Expense
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id'       => $this->tenant->id,
                'created_by'      => $this->expenseCreator->id,
                'status'          => InvoiceStatus::PROCESSING,
                'approval_status' => ApprovalStatus::NOT_REQUIRED,
                'total_gross'     => 1000.00,
            ]);
        });
    }

    private function createSingleStepWorkflow(): ApprovalWorkflow
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            $workflow = ApprovalWorkflow::factory()->create([
                'tenant_id'  => $this->tenant->id,
                'name'       => 'Single Step Workflow',
                'is_active'  => true,
                'amount_min' => 0,
                'amount_max' => 2000,
                'priority'   => 1,
            ]);

            $step = ApprovalWorkflowStep::factory()->create([
                'workflow_id'           => $workflow->id,
                'step_order'            => 1,
                'name'                  => 'Manager Approval',
                'require_all_approvers' => false,
                'min_approvers'         => 1,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver1->id,
            ]);

            return $workflow;
        });
    }

    private function createMultiStepWorkflow(): ApprovalWorkflow
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            $workflow = ApprovalWorkflow::factory()->create([
                'tenant_id'  => $this->tenant->id,
                'name'       => 'Multi Step Workflow',
                'is_active'  => true,
                'amount_min' => 0,
                'amount_max' => 2000,
                'priority'   => 1,
            ]);

            // First step
            $step1 = ApprovalWorkflowStep::factory()->create([
                'workflow_id'           => $workflow->id,
                'step_order'            => 1,
                'name'                  => 'Manager Approval',
                'require_all_approvers' => false,
                'min_approvers'         => 1,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step1->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver1->id,
            ]);

            // Second step
            $step2 = ApprovalWorkflowStep::factory()->create([
                'workflow_id'           => $workflow->id,
                'step_order'            => 2,
                'name'                  => 'Director Approval',
                'require_all_approvers' => false,
                'min_approvers'         => 1,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step2->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver2->id,
            ]);

            return $workflow;
        });
    }

    private function createParallelApprovalWorkflow(): ApprovalWorkflow
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            $workflow = ApprovalWorkflow::factory()->create([
                'tenant_id'  => $this->tenant->id,
                'name'       => 'Parallel Approval Workflow',
                'is_active'  => true,
                'amount_min' => 0,
                'amount_max' => 2000,
                'priority'   => 1,
            ]);

            $step = ApprovalWorkflowStep::factory()->create([
                'workflow_id'           => $workflow->id,
                'step_order'            => 1,
                'name'                  => 'Parallel Approval',
                'require_all_approvers' => false,
                'min_approvers'         => 2, // Require 2 out of 3 approvers
            ]);

            // Create 3 approvers
            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver1->id,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver2->id,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver3->id,
            ]);

            return $workflow;
        });
    }

    private function createRequireAllApproversWorkflow(): ApprovalWorkflow
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            $workflow = ApprovalWorkflow::factory()->create([
                'tenant_id'  => $this->tenant->id,
                'name'       => 'Require All Approvers Workflow',
                'is_active'  => true,
                'amount_min' => 0,
                'amount_max' => 2000,
                'priority'   => 1,
            ]);

            $step = ApprovalWorkflowStep::factory()->create([
                'workflow_id'           => $workflow->id,
                'step_order'            => 1,
                'name'                  => 'All Approvers Required',
                'require_all_approvers' => true,
                'min_approvers'         => 1,
            ]);

            // Create 3 approvers - all required
            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver1->id,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver2->id,
            ]);

            ApprovalStepApprover::factory()->create([
                'step_id'        => $step->id,
                'approver_type'  => ApproverType::USER,
                'approver_value' => $this->approver3->id,
            ]);

            return $workflow;
        });
    }
}
