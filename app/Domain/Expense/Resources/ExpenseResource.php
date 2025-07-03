<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Common\Resources\TagResource;
use App\Domain\Expense\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expense
 */
class ExpenseResource extends JsonResource
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
            'id'                  => $this->id,
            'tenantId'            => $this->tenant_id,
            'type'                => $this->type->value,
            'status'              => $this->status->value, // Backward compatibility
            'statusInfo'          => [
                'general'    => $this->status?->value,
                'ocr'        => $this->ocr_status?->value,
                'allocation' => $this->allocation_status?->value,
                'approval'   => $this->approval_status?->value,
                'delivery'   => $this->delivery_status?->value,
                'payment'    => $this->payment_status?->value,
            ],
            'number'              => $this->number,
            'totalNet'            => $this->total_net->toFloat(),
            'totalTax'            => $this->total_tax->toFloat(),
            'totalGross'          => $this->total_gross->toFloat(),
            'currency'            => $this->currency,
            'exchangeRate'        => $this->exchange_rate->toFloat(),
            'seller'              => $this->seller->toArray(),
            'buyer'               => $this->buyer->toArray(),
            'body'                => $this->body->toArray(),
            'payment'             => $this->payment->toArray(),
            'options'             => $this->options->toArray(),
            'issueDate'           => $this->issue_date?->toDateString(),
            'createdAt'           => $this->created_at?->toIso8601String(),
            'updatedAt'           => $this->updated_at?->toIso8601String(),
            'tags'                => TagResource::collection($this->tags),
            // Allocation information
            'totalAllocated'       => $this->when($this->relationLoaded('allocations'), fn () => $this->total_allocated->toFloat()),
            'remainingToAllocate'  => $this->when($this->relationLoaded('allocations'), fn () => $this->remaining_to_allocate->toFloat()),
            'isFullyAllocated'     => $this->when($this->relationLoaded('allocations'), fn () => $this->is_fully_allocated),
            'isPartiallyAllocated' => $this->when($this->relationLoaded('allocations'), fn () => $this->is_partially_allocated),
            'hasAllocations'       => $this->when($this->relationLoaded('allocations'), fn () => $this->hasAllocations()),
            'allocations'          => ExpenseAllocationResource::collection($this->whenLoaded('allocations')),
        ];
    }
}
