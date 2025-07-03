<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for expense allocation summary data.
 *
 * @mixin Expense
 */
class ExpenseAllocationSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Expense $this->resource */
        return [
            'allocations'         => ExpenseAllocationResource::collection($this->allocations),
            'expenseTotal'        => $this->total_gross->toFloat(),
            'totalAllocated'      => $this->total_allocated->toFloat(),
            'remainingToAllocate' => $this->remaining_to_allocate->toFloat(),
            'isFullyAllocated'    => $this->is_fully_allocated,
        ];
    }
}
