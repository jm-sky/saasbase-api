<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\Models\AllocationDimension;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AllocationDimension
 */
class AllocationDimensionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var AllocationDimension $this->resource */
        return [
            'id'                   => $this->id,
            'allocationId'         => $this->allocation_id,
            'dimensionType'        => $this->dimension_type->value,
            'dimensionTypeLabel'   => $this->dimension_type->label(),
            'dimensionTypeLabelEN' => $this->dimension_type->labelEN(),
            'dimensionId'          => $this->dimension_id,
            'isConfigurable'       => $this->dimension_type->isConfigurable(),
            'isAlwaysVisible'      => $this->dimension_type->isAlwaysVisible(),
            'createdAt'            => $this->created_at?->toIso8601String(),
            'updatedAt'            => $this->updated_at?->toIso8601String(),
            // Include the actual dimension entity if loaded
            'dimensionEntity' => $this->when(
                $this->relationLoaded('dimensionable') && $this->dimensionable,
                function () {
                    return new DimensionItemResource($this->dimensionable);
                }
            ),
        ];
    }
}
