<?php

namespace App\Domain\Expense\Actions;

use App\Domain\Expense\DTOs\AllocationDataDTO;
use App\Domain\Expense\DTOs\AllocationDimensionDTO;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Expense\Models\AllocationDimension;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Models\ExpenseAllocation;
use App\Domain\Expense\Services\DimensionVisibilityService;
use App\Domain\Financial\Enums\InvoiceStatus;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;

class AllocateExpenseAction
{
    public function __construct(
        private DimensionVisibilityService $dimensionVisibilityService
    ) {
    }

    /**
     * Allocate an expense across multiple allocations with dimensions.
     *
     * @param AllocationDataDTO[] $allocations Array of allocation DTOs
     *
     * @throws \InvalidArgumentException
     */
    public function execute(Expense $expense, array $allocations): void
    {
        $this->validateAllocations($expense, $allocations);

        DB::transaction(function () use ($expense, $allocations) {
            // Clear existing allocations
            $expense->allocations()->delete();

            foreach ($allocations as $allocationData) {
                $allocation = ExpenseAllocation::create([
                    'tenant_id'  => $expense->tenant_id,
                    'expense_id' => $expense->id,
                    'amount'     => $allocationData->amount,
                    'note'       => $allocationData->note,
                ]);

                // Create dimension associations
                foreach ($allocationData->dimensions as $dimension) {
                    $this->createDimensionAssociation($allocation, $dimension);
                }
            }

            // Update expense status - allocation completed
            $expense->update(['general_status' => InvoiceStatus::PROCESSING]);
        });
    }

    /**
     * Validate allocation data before processing.
     *
     * @param AllocationDataDTO[] $allocations
     */
    private function validateAllocations(Expense $expense, array $allocations): void
    {
        if (empty($allocations)) {
            throw new \InvalidArgumentException('At least one allocation is required');
        }

        $totalAmount       = BigDecimal::zero();
        $enabledDimensions = $this->dimensionVisibilityService
            ->getEnabledDimensionsForTenant($expense->tenant_id)
        ;

        foreach ($allocations as $index => $allocation) {
            // Validate amount
            if ($allocation->amount->isLessThanOrEqualTo(BigDecimal::zero())) {
                throw new \InvalidArgumentException("Allocation amount must be greater than zero at index {$index}");
            }

            $totalAmount = $totalAmount->plus($allocation->amount);

            // Validate dimensions
            $this->validateDimensions($allocation->dimensions, $enabledDimensions, $index);
        }

        // Check total amount doesn't exceed expense total
        if ($totalAmount->isGreaterThan($expense->total_gross)) {
            throw new \InvalidArgumentException("Total allocation amount ({$totalAmount}) exceeds expense total ({$expense->total_gross})");
        }
    }

    /**
     * Validate dimension data.
     *
     * @param AllocationDimensionDTO[] $dimensions
     */
    private function validateDimensions(array $dimensions, $enabledDimensions, int $allocationIndex): void
    {
        foreach ($dimensions as $dimensionIndex => $dimension) {
            // Check if dimension is enabled for tenant
            $isDimensionEnabled = $enabledDimensions->contains($dimension->type);

            if (!$isDimensionEnabled) {
                throw new \InvalidArgumentException("Dimension type '{$dimension->type->value}' is not enabled for this tenant");
            }

            // Validate dimension exists
            $this->validateDimensionExists($dimension->type, $dimension->id, $allocationIndex);
        }
    }

    /**
     * Validate that the dimension entity exists.
     */
    private function validateDimensionExists(AllocationDimensionType $dimensionType, string $dimensionId, int $allocationIndex): void
    {
        $modelClass = $dimensionType->getMorphClass();

        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Dimension model class {$modelClass} does not exist for type {$dimensionType->value}");
        }

        $exists = null !== $modelClass::find($dimensionId);

        if (!$exists) {
            throw new \InvalidArgumentException("Dimension entity with ID '{$dimensionId}' does not exist for type '{$dimensionType->value}' at allocation {$allocationIndex}");
        }
    }

    /**
     * Create dimension association for allocation.
     */
    private function createDimensionAssociation(ExpenseAllocation $allocation, AllocationDimensionDTO $dimension): void
    {
        AllocationDimension::create([
            'allocation_id'  => $allocation->id,
            'dimension_type' => $dimension->type,
            'dimension_id'   => $dimension->id,
        ]);
    }

    /**
     * Auto-allocate expense with basic allocation (useful for simple cases).
     *
     * @param AllocationDimensionDTO[] $basicDimensions
     */
    public function autoAllocate(Expense $expense, array $basicDimensions = []): void
    {
        $allocation = new AllocationDataDTO(
            amount: $expense->total_gross,
            note: 'Auto-allocated',
            dimensions: $basicDimensions,
        );

        $this->execute($expense, [$allocation]);
    }

    /**
     * Check if expense can be allocated.
     */
    public function canAllocate(Expense $expense): bool
    {
        return InvoiceStatus::PROCESSING === $expense->general_status
               || InvoiceStatus::DRAFT === $expense->general_status;
    }

    /**
     * Get suggested allocations based on expense data (future enhancement).
     *
     * @return AllocationDataDTO[]
     */
    public function getSuggestedAllocations(Expense $expense): array
    {
        // This could be enhanced with ML/AI suggestions based on:
        // - Historical allocations for similar expenses
        // - Vendor patterns
        // - Amount ranges
        // - Project assignments

        return [
            new AllocationDataDTO(
                amount: $expense->total_gross,
                note: 'Suggested allocation',
                dimensions: [],
            ),
        ];
    }
}
