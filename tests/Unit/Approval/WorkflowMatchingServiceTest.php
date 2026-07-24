<?php

namespace Tests\Unit\Approval;

use App\Domain\Approval\Models\ApprovalWorkflow;
use App\Domain\Approval\Services\WorkflowMatchingService;
use App\Domain\Expense\Models\AllocationDimension;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Models\ExpenseAllocation;
use App\Domain\Financial\Models\AllocationTransactionType;
use App\Domain\Projects\Models\Project;
use App\Domain\Tenant\Models\Tenant;
use Brick\Math\BigDecimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 *
 * @covers \App\Domain\Approval\Services\WorkflowMatchingService
 */
#[CoversClass(WorkflowMatchingService::class)]
class WorkflowMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowMatchingService $service;

    private Tenant $tenant;

    private Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WorkflowMatchingService;
        $this->tenant = Tenant::factory()->create();

        $this->expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
                'total_gross' => BigDecimal::of('1500.00'),
            ]);
        });
    }

    #[Test]
    public function finds_workflow_matching_amount_range()
    {
        // Create workflow that matches our expense amount (1500)
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            return ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function returns_null_when_amount_below_minimum()
    {
        // Create workflow with minimum above our expense amount
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('2000.00'), // Above 1500
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function returns_null_when_amount_above_maximum()
    {
        // Create workflow with maximum below our expense amount
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_max' => BigDecimal::of('1000.00'), // Below 1500
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function selects_highest_priority_when_multiple_match()
    {
        // Create two workflows that both match
        [$lowPriorityWorkflow, $highPriorityWorkflow] = Tenant::bypassTenant($this->tenant->id, function () {
            $lowPriorityWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority' => 1,
                'name' => 'Low Priority',
            ]);

            $highPriorityWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority' => 5,
                'name' => 'High Priority',
            ]);

            return [$lowPriorityWorkflow, $highPriorityWorkflow];
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($highPriorityWorkflow->id, $result->id);
        $this->assertEquals('High Priority', $result->name);
    }

    #[Test]
    public function returns_null_when_no_workflows_exist()
    {
        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function skips_inactive_workflows()
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'is_active' => false, // Inactive
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function matches_workflow_with_no_amount_restrictions()
    {
        // Workflow with no amount limits should match any expense
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            return ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => null,
                'match_amount_max' => null,
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function matches_workflow_with_allocation_conditions_has_any()
    {
        // Create expense with project allocation
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id' => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'PR', // PROJECT
                'dimension_id' => $project->id,
            ]);

            // Workflow that requires project dimension
            return ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'has_any'],
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function fails_allocation_condition_when_dimension_missing()
    {
        // Create expense without any allocations

        // Workflow that requires project dimension
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'has_any'],
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function matches_workflow_with_allocation_conditions_equals()
    {
        // Create expense with specific project allocation
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id' => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'PR', // PROJECT
                'dimension_id' => $project->id,
            ]);

            // Workflow that requires specific project
            return ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'equals', 'value' => $project->id],
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function fails_allocation_condition_equals_wrong_value()
    {
        // Create expense with one project
        Tenant::bypassTenant($this->tenant->id, function () {
            $project1 = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $project2 = Project::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id' => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id' => $project1->id,
            ]);

            // Workflow that requires different project
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'equals', 'value' => $project2->id],
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function matches_workflow_with_allocation_conditions_in()
    {
        // Create expense with project allocation
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project1 = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $project2 = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $project3 = Project::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id' => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id' => $project2->id, // This one is in our list
            ]);

            // Workflow that accepts multiple projects
            return ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'in', 'values' => [$project1->id, $project2->id]],
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function matches_workflow_with_multiple_conditions()
    {
        // Create expense with both project and transaction type allocations
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $transactionType = AllocationTransactionType::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id' => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id' => $project->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'RTR',
                'dimension_id' => $transactionType->id,
            ]);

            // Workflow that requires BOTH project and transaction type
            return ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'has_any'],
                    ['dimension_type' => 'RTR', 'operator' => 'has_any'],
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function fails_when_one_condition_fails_in_multiple_conditions()
    {
        // Create expense with only project allocation (missing transaction type)
        Tenant::bypassTenant($this->tenant->id, function () {
            $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id' => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id' => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id' => $project->id,
            ]);

            // Workflow that requires BOTH project and transaction type
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_conditions' => [
                    ['dimension_type' => 'PR', 'operator' => 'has_any'], // This passes
                    ['dimension_type' => 'RTR', 'operator' => 'has_any'], // This fails
                ],
                'priority' => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function gets_potential_workflows_for_debugging()
    {
        // Create matching and non-matching workflows
        [$matchingWorkflow, $nonMatchingWorkflow] = Tenant::bypassTenant($this->tenant->id, function () {
            $matchingWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority' => 1,
                'name' => 'Matching',
            ]);

            $nonMatchingWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('3000.00'), // Above our amount
                'priority' => 2,
                'name' => 'Non-matching',
            ]);

            return [$matchingWorkflow, $nonMatchingWorkflow];
        });

        $results = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->getPotentialWorkflows($this->expense);
        });

        $this->assertCount(2, $results);

        // Find workflows by name instead of relying on order
        $matchingResult = $results->first(fn ($result) => $result['workflow']->name === 'Matching');
        $this->assertNotNull($matchingResult);
        $this->assertEquals('Matching', $matchingResult['workflow']->name);
        $this->assertTrue($matchingResult['matches']);
        $this->assertTrue($matchingResult['amount_matches']);
        $this->assertTrue($matchingResult['conditions_match']);

        $nonMatchingResult = $results->first(fn ($result) => $result['workflow']->name === 'Non-matching');
        $this->assertNotNull($nonMatchingResult);
        $this->assertEquals('Non-matching', $nonMatchingResult['workflow']->name);
        $this->assertFalse($nonMatchingResult['matches']);
        $this->assertFalse($nonMatchingResult['amount_matches']);
        $this->assertTrue($nonMatchingResult['conditions_match']); // No conditions = always match
    }

    #[Test]
    public function checks_if_workflows_exist_for_tenant()
    {
        // Initially no workflows
        $hasWorkflows = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->hasWorkflowsForTenant($this->tenant->id);
        });
        $this->assertFalse($hasWorkflows);

        // Create workflow
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $hasWorkflows = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->hasWorkflowsForTenant($this->tenant->id);
        });
        $this->assertTrue($hasWorkflows);
    }

    #[Test]
    public function gets_workflow_statistics()
    {
        // Create various workflows
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'is_active' => true,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_conditions' => ['some' => 'condition'],
            ]);

            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'is_active' => false,
                'match_amount_max' => BigDecimal::of('5000.00'),
            ]);

            ApprovalWorkflow::factory()->create([
                'tenant_id' => $this->tenant->id,
                'is_active' => true,
            ]);
        });

        $stats = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->getWorkflowStats($this->tenant->id);
        });

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['active']);
        $this->assertEquals(1, $stats['inactive']);
        $this->assertEquals(2, $stats['with_amount_conditions']);
        $this->assertEquals(1, $stats['with_allocation_conditions']);
    }

    #[Test]
    public function only_finds_workflows_for_correct_tenant()
    {
        $otherTenant = Tenant::factory()->create();

        // Create workflow for other tenant
        Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            ApprovalWorkflow::factory()->create([
                'tenant_id' => $otherTenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }
}
