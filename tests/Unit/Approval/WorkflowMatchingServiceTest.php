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

        $this->service = new WorkflowMatchingService();
        $this->tenant  = Tenant::factory()->create();

        $this->expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id'   => $this->tenant->id,
                'total_gross' => BigDecimal::of('1500.00'),
            ]);
        });
    }

    #[Test]
    public function findsWorkflowMatchingAmountRange()
    {
        // Create workflow that matches our expense amount (1500)
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            return ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority'         => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function returnsNullWhenAmountBelowMinimum()
    {
        // Create workflow with minimum above our expense amount
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('2000.00'), // Above 1500
                'priority'         => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function returnsNullWhenAmountAboveMaximum()
    {
        // Create workflow with maximum below our expense amount
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_max' => BigDecimal::of('1000.00'), // Below 1500
                'priority'         => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function selectsHighestPriorityWhenMultipleMatch()
    {
        // Create two workflows that both match
        [$lowPriorityWorkflow, $highPriorityWorkflow] = Tenant::bypassTenant($this->tenant->id, function () {
            $lowPriorityWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority'         => 1,
                'name'             => 'Low Priority',
            ]);

            $highPriorityWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority'         => 5,
                'name'             => 'High Priority',
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
    public function returnsNullWhenNoWorkflowsExist()
    {
        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function skipsInactiveWorkflows()
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'is_active'        => false, // Inactive
                'priority'         => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNull($result);
    }

    #[Test]
    public function matchesWorkflowWithNoAmountRestrictions()
    {
        // Workflow with no amount limits should match any expense
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            return ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => null,
                'match_amount_max' => null,
                'priority'         => 1,
            ]);
        });

        $result = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->findMatchingWorkflow($this->expense);
        });

        $this->assertNotNull($result);
        $this->assertEquals($workflow->id, $result->id);
    }

    #[Test]
    public function matchesWorkflowWithAllocationConditionsHasAny()
    {
        // Create expense with project allocation
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project    = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id'  => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'PR', // PROJECT
                'dimension_id'   => $project->id,
            ]);

            // Workflow that requires project dimension
            return ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function failsAllocationConditionWhenDimensionMissing()
    {
        // Create expense without any allocations

        // Workflow that requires project dimension
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function matchesWorkflowWithAllocationConditionsEquals()
    {
        // Create expense with specific project allocation
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project    = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id'  => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'PR', // PROJECT
                'dimension_id'   => $project->id,
            ]);

            // Workflow that requires specific project
            return ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function failsAllocationConditionEqualsWrongValue()
    {
        // Create expense with one project
        Tenant::bypassTenant($this->tenant->id, function () {
            $project1 = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $project2 = Project::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id'  => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id'   => $project1->id,
            ]);

            // Workflow that requires different project
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function matchesWorkflowWithAllocationConditionsIn()
    {
        // Create expense with project allocation
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project1 = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $project2 = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $project3 = Project::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id'  => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id'   => $project2->id, // This one is in our list
            ]);

            // Workflow that accepts multiple projects
            return ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function matchesWorkflowWithMultipleConditions()
    {
        // Create expense with both project and transaction type allocations
        $workflow = Tenant::bypassTenant($this->tenant->id, function () {
            $project         = Project::factory()->create(['tenant_id' => $this->tenant->id]);
            $transactionType = AllocationTransactionType::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id'  => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id'   => $project->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'RTR',
                'dimension_id'   => $transactionType->id,
            ]);

            // Workflow that requires BOTH project and transaction type
            return ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function failsWhenOneConditionFailsInMultipleConditions()
    {
        // Create expense with only project allocation (missing transaction type)
        Tenant::bypassTenant($this->tenant->id, function () {
            $project = Project::factory()->create(['tenant_id' => $this->tenant->id]);

            $allocation = ExpenseAllocation::factory()->create([
                'expense_id' => $this->expense->id,
                'tenant_id'  => $this->tenant->id,
            ]);

            AllocationDimension::factory()->create([
                'allocation_id'  => $allocation->id,
                'dimension_type' => 'PR',
                'dimension_id'   => $project->id,
            ]);

            // Workflow that requires BOTH project and transaction type
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
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
    public function getsPotentialWorkflowsForDebugging()
    {
        // Create matching and non-matching workflows
        [$matchingWorkflow, $nonMatchingWorkflow] = Tenant::bypassTenant($this->tenant->id, function () {
            $matchingWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_amount_max' => BigDecimal::of('2000.00'),
                'priority'         => 1,
                'name'             => 'Matching',
            ]);

            $nonMatchingWorkflow = ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'match_amount_min' => BigDecimal::of('3000.00'), // Above our amount
                'priority'         => 2,
                'name'             => 'Non-matching',
            ]);

            return [$matchingWorkflow, $nonMatchingWorkflow];
        });

        $results = Tenant::bypassTenant($this->tenant->id, function () {
            return $this->service->getPotentialWorkflows($this->expense);
        });

        $this->assertCount(2, $results);

        // Find workflows by name instead of relying on order
        $matchingResult = $results->first(fn ($result) => 'Matching' === $result['workflow']->name);
        $this->assertNotNull($matchingResult);
        $this->assertEquals('Matching', $matchingResult['workflow']->name);
        $this->assertTrue($matchingResult['matches']);
        $this->assertTrue($matchingResult['amount_matches']);
        $this->assertTrue($matchingResult['conditions_match']);

        $nonMatchingResult = $results->first(fn ($result) => 'Non-matching' === $result['workflow']->name);
        $this->assertNotNull($nonMatchingResult);
        $this->assertEquals('Non-matching', $nonMatchingResult['workflow']->name);
        $this->assertFalse($nonMatchingResult['matches']);
        $this->assertFalse($nonMatchingResult['amount_matches']);
        $this->assertTrue($nonMatchingResult['conditions_match']); // No conditions = always match
    }

    #[Test]
    public function checksIfWorkflowsExistForTenant()
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
    public function getsWorkflowStatistics()
    {
        // Create various workflows
        Tenant::bypassTenant($this->tenant->id, function () {
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'is_active'        => true,
                'match_amount_min' => BigDecimal::of('1000.00'),
                'match_conditions' => ['some' => 'condition'],
            ]);

            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $this->tenant->id,
                'is_active'        => false,
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
    public function onlyFindsWorkflowsForCorrectTenant()
    {
        $otherTenant = Tenant::factory()->create();

        // Create workflow for other tenant
        Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            ApprovalWorkflow::factory()->create([
                'tenant_id'        => $otherTenant->id,
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
