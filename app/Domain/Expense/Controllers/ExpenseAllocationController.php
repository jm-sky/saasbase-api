<?php

namespace App\Domain\Expense\Controllers;

use App\Domain\Expense\Actions\AllocateExpenseAction;
use App\Domain\Expense\DTOs\AllocationDataDTO;
use App\Domain\Expense\DTOs\AllocationDimensionDTO;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Models\ExpenseAllocation;
use App\Domain\Expense\Requests\AutoAllocateExpenseRequest;
use App\Domain\Expense\Requests\StoreExpenseAllocationRequest;
use App\Domain\Expense\Resources\AllocationSuggestionsResource;
use App\Domain\Expense\Resources\ExpenseAllocationResource;
use App\Domain\Expense\Resources\ExpenseAllocationSummaryResource;
use App\Domain\Expense\Services\DimensionVisibilityService;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExpenseAllocationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private AllocateExpenseAction $allocateAction,
        private DimensionVisibilityService $dimensionService
    ) {
    }

    /**
     * Get all allocations for an expense.
     */
    public function index(Expense $expense): AnonymousResourceCollection
    {
        $allocations = $expense->allocations()
            ->with(['dimensions.dimensionable'])
            ->orderBy('created_at')
            ->get()
        ;

        return ExpenseAllocationResource::collection($allocations);
    }

    /**
     * Create or update allocations for an expense.
     */
    public function store(StoreExpenseAllocationRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        if (!$this->allocateAction->canAllocate($expense)) {
            return response()->json([
                'message'       => 'This expense cannot be allocated in its current status',
                'currentStatus' => $expense->status->value,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();

        try {
            // Convert validated data to DTOs
            $allocationDTOs = AllocationDataDTO::collectFromArray($validated['allocations']);

            $this->allocateAction->execute($expense, $allocationDTOs);

            // Reload expense with allocations
            $expense->load(['allocations.dimensions.dimensionable']);

            return response()->json([
                'message' => 'Expense allocated successfully',
                'data'    => new ExpenseAllocationSummaryResource($expense),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Allocation validation failed',
                'error'   => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Auto-allocate expense with basic allocation.
     */
    public function autoAllocate(AutoAllocateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        if (!$this->allocateAction->canAllocate($expense)) {
            return response()->json([
                'message'       => 'This expense cannot be allocated in its current status',
                'currentStatus' => $expense->status->value,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();

        try {
            // Convert dimensions to DTOs
            $dimensionDTOs = [];

            if (isset($validated['dimensions'])) {
                $dimensionDTOs = array_map(
                    fn (array $dimensionData) => AllocationDimensionDTO::fromArray($dimensionData),
                    $validated['dimensions']
                );
            }

            $this->allocateAction->autoAllocate($expense, $dimensionDTOs);

            // Reload expense with allocations
            $expense->load(['allocations.dimensions.dimensionable']);

            return response()->json([
                'message' => 'Expense auto-allocated successfully',
                'data'    => new ExpenseAllocationSummaryResource($expense),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Auto-allocation failed',
                'error'   => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Get suggested allocations for an expense.
     */
    public function suggestions(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        $suggestions       = collect($this->allocateAction->getSuggestedAllocations($expense));
        $enabledDimensions = $this->dimensionService->getEnabledDimensionsForTenant($expense->tenant_id);

        return response()->json([
            'data' => new AllocationSuggestionsResource($expense, $suggestions, $enabledDimensions),
        ]);
    }

    /**
     * Delete a specific allocation.
     */
    public function destroy(Expense $expense, ExpenseAllocation $allocation): JsonResponse
    {
        $this->authorize('update', $expense);

        // Verify allocation belongs to expense
        if ($allocation->expense_id !== $expense->id) {
            return response()->json([
                'message' => 'Allocation does not belong to this expense',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $allocation->delete();

        return response()->json([
            'message' => 'Allocation deleted successfully',
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Clear all allocations for an expense.
     */
    public function clear(Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        $expense->allocations()->delete();

        // Update expense status back to processing
        $expense->update(['status' => \App\Domain\Financial\Enums\InvoiceStatus::PROCESSING]);

        return response()->json([
            'message' => 'All allocations cleared successfully',
        ], Response::HTTP_NO_CONTENT);
    }
}
