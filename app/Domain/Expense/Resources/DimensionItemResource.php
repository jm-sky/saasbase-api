<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\Contracts\AllocationDimensionInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for individual dimension items (TransactionType, CostType, etc.).
 * This handles the generic structure that all dimension models share.
 *
 * @mixin AllocationDimensionInterface
 */
class DimensionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var AllocationDimensionInterface $this->resource */
        return [
            'id'          => $this->getId(),
            'code'        => $this->getCode(),
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
            'isGlobal'    => $this->isGlobal(),
            'displayName' => $this->getDisplayName(),
            'isActive'    => $this->getIsActive(),
        ];
    }
}
