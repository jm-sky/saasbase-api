<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\Models\ExpenseAllocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExpenseAllocation
 */
class ExpenseAllocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var ExpenseAllocation $this->resource */
        return [
            'id'            => $this->id,
            'tenantId'      => $this->tenant_id,
            'expenseId'     => $this->expense_id,
            'amount'        => $this->amount->toFloat(),
            'note'          => $this->note,
            'status'        => $this->status->value,
            'statusLabel'   => $this->status->label(),
            'statusLabelPL' => $this->status->labelPL(),
            'createdAt'     => $this->created_at?->toIso8601String(),
            'updatedAt'     => $this->updated_at?->toIso8601String(),
            'dimensions'    => AllocationDimensionResource::collection($this->whenLoaded('dimensions')),
            'expense'       => new ExpenseResource($this->whenLoaded('expense')),
        ];
    }
}
