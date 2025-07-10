<?php

namespace App\Domain\Approval\Services;

use App\Domain\Approval\Models\ApprovalWorkflow;
use App\Domain\Expense\Models\Expense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class WorkflowMatchingService
{
    /**
     * Find the best matching workflow for an expense.
     * Returns null if no workflow matches (indicating auto-approval).
     */
    public function findMatchingWorkflow(Expense $expense): ?ApprovalWorkflow
    {
        Log::info('Finding workflow for expense', [
            'expense_id' => $expense->id,
            'tenant_id'  => $expense->tenant_id,
            'amount'     => $expense->total_gross->toFloat(),
        ]);

        // Get all active workflows for the tenant, ordered by priority (highest first)
        // @phpstan-ignore-next-line
        $workflows = ApprovalWorkflow::withoutTenant()
            ->where('tenant_id', $expense->tenant_id)
            ->active()
            ->byPriority()
            ->get()
        ;

        if ($workflows->isEmpty()) {
            Log::info('No workflows found for tenant', [
                'tenant_id'  => $expense->tenant_id,
                'expense_id' => $expense->id,
            ]);

            return null;
        }

        // Find the first workflow that matches all criteria
        foreach ($workflows as $workflow) {
            if ($this->workflowMatches($expense, $workflow)) {
                Log::info('Workflow matched for expense', [
                    'expense_id'    => $expense->id,
                    'workflow_id'   => $workflow->id,
                    'workflow_name' => $workflow->name,
                    'priority'      => $workflow->priority,
                ]);

                return $workflow;
            }
        }

        Log::info('No matching workflow found - expense will be auto-approved', [
            'expense_id'        => $expense->id,
            'tenant_id'         => $expense->tenant_id,
            'workflows_checked' => $workflows->count(),
        ]);

        return null;
    }

    /**
     * Check if a specific workflow matches the expense criteria.
     */
    public function workflowMatches(Expense $expense, ApprovalWorkflow $workflow): bool
    {
        // Check if workflow is active
        if (!$workflow->is_active) {
            return false;
        }

        // Check amount range criteria
        if (!$this->matchesAmountCriteria($expense, $workflow)) {
            Log::debug('Workflow amount criteria not met', [
                'workflow_id'    => $workflow->id,
                'expense_amount' => $expense->total_gross->toFloat(),
                'min_amount'     => $workflow->match_amount_min?->toFloat(),
                'max_amount'     => $workflow->match_amount_max?->toFloat(),
            ]);

            return false;
        }

        // Check allocation dimension conditions
        if (!$this->matchesAllocationConditions($expense, $workflow)) {
            Log::debug('Workflow allocation conditions not met', [
                'workflow_id' => $workflow->id,
                'expense_id'  => $expense->id,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Check if expense amount matches workflow amount criteria.
     */
    private function matchesAmountCriteria(Expense $expense, ApprovalWorkflow $workflow): bool
    {
        $amount = $expense->total_gross;

        // Check minimum amount
        if ($workflow->match_amount_min && $amount->isLessThan($workflow->match_amount_min)) {
            return false;
        }

        // Check maximum amount
        if ($workflow->match_amount_max && $amount->isGreaterThan($workflow->match_amount_max)) {
            return false;
        }

        return true;
    }

    /**
     * Check if expense allocation dimensions match workflow conditions.
     */
    private function matchesAllocationConditions(Expense $expense, ApprovalWorkflow $workflow): bool
    {
        // If no match conditions are defined, workflow matches all allocations
        if (!$workflow->match_conditions || empty($workflow->match_conditions)) {
            return true;
        }

        // Load expense allocations with dimensions
        $expense->load(['allocations.dimensions.dimensionable']);

        return $this->evaluateMatchConditions($expense, $workflow->match_conditions);
    }

    /**
     * Evaluate complex match conditions against expense allocations.
     */
    private function evaluateMatchConditions(Expense $expense, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (!$this->evaluateSingleCondition($expense, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single match condition.
     *
     * Condition format examples:
     * - ['dimension_type' => 'PROJECT', 'operator' => 'has_any']
     * - ['dimension_type' => 'PROJECT', 'operator' => 'equals', 'value' => 'project_id']
     * - ['dimension_type' => 'COST_TYPE', 'operator' => 'in', 'values' => ['cost_type_1', 'cost_type_2']]
     * - ['dimension_type' => 'STRUCTURE', 'operator' => 'not_equals', 'value' => 'unit_id']
     */
    private function evaluateSingleCondition(Expense $expense, array $condition): bool
    {
        $dimensionType = $condition['dimension_type'] ?? null;
        $operator      = $condition['operator'] ?? 'has_any';

        if (!$dimensionType) {
            Log::warning('Match condition missing dimension_type', [
                'condition'  => $condition,
                'expense_id' => $expense->id,
            ]);

            return false;
        }

        // Get all dimension values for this type across all allocations
        $dimensionValues = $this->getExpenseDimensionValues($expense, $dimensionType);

        return match ($operator) {
            'has_any'      => $dimensionValues->isNotEmpty(),
            'has_none'     => $dimensionValues->isEmpty(),
            'equals'       => $this->evaluateEquals($dimensionValues, $condition['value'] ?? null),
            'not_equals'   => !$this->evaluateEquals($dimensionValues, $condition['value'] ?? null),
            'in'           => $this->evaluateIn($dimensionValues, $condition['values'] ?? []),
            'not_in'       => !$this->evaluateIn($dimensionValues, $condition['values'] ?? []),
            'count_gte'    => $dimensionValues->count() >= ($condition['count'] ?? 1),
            'count_lte'    => $dimensionValues->count() <= ($condition['count'] ?? 0),
            'count_equals' => $dimensionValues->count() === ($condition['count'] ?? 0),
            default        => false,
        };
    }

    /**
     * Get all dimension values of a specific type from expense allocations.
     */
    private function getExpenseDimensionValues(Expense $expense, string $dimensionType): \Illuminate\Support\Collection
    {
        $values = collect();

        foreach ($expense->allocations as $allocation) {
            foreach ($allocation->dimensions as $dimension) {
                if ($dimension->dimension_type->value === $dimensionType) {
                    $values->push($dimension->dimension_id);
                }
            }
        }

        return $values->unique();
    }

    /**
     * Evaluate 'equals' operator.
     */
    private function evaluateEquals(\Illuminate\Support\Collection $dimensionValues, ?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return $dimensionValues->contains($value);
    }

    /**
     * Evaluate 'in' operator.
     */
    private function evaluateIn(\Illuminate\Support\Collection $dimensionValues, array $values): bool
    {
        if (empty($values)) {
            return false;
        }

        return $dimensionValues->intersect($values)->isNotEmpty();
    }

    /**
     * Get all workflows that could potentially match an expense (for debugging/preview).
     */
    public function getPotentialWorkflows(Expense $expense): \Illuminate\Support\Collection
    {
        // @phpstan-ignore-next-line
        $workflows = ApprovalWorkflow::withoutTenant()
            ->where('tenant_id', $expense->tenant_id)
            ->active()
            ->byPriority()
            ->get()
        ;

        return $workflows->map(function (ApprovalWorkflow $workflow) use ($expense) {
            return [
                'workflow'         => $workflow,
                'matches'          => $this->workflowMatches($expense, $workflow),
                'amount_matches'   => $this->matchesAmountCriteria($expense, $workflow),
                'conditions_match' => $this->matchesAllocationConditions($expense, $workflow),
            ];
        });
    }

    /**
     * Check if any workflow exists for the tenant.
     */
    public function hasWorkflowsForTenant(string $tenantId): bool
    {
        // @phpstan-ignore-next-line
        return ApprovalWorkflow::withoutTenant()
            ->where('tenant_id', $tenantId)
            ->active()
            ->exists()
        ;
    }

    /**
     * Get workflow statistics for a tenant.
     */
    public function getWorkflowStats(string $tenantId): array
    {
        /** @var Collection<int, ApprovalWorkflow> $workflows */
        $workflows = ApprovalWorkflow::withoutTenant()->where('tenant_id', $tenantId)->get();

        return [
            'total'                      => $workflows->count(),
            'active'                     => $workflows->where('is_active', true)->count(),
            'inactive'                   => $workflows->where('is_active', false)->count(),
            'with_amount_conditions'     => $workflows->filter(fn ($w) => $w->match_amount_min || $w->match_amount_max)->count(),
            'with_allocation_conditions' => $workflows->filter(fn ($w) => !empty($w->match_conditions))->count(),
        ];
    }
}
