<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\Enums\AllocationDimensionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for AllocationDimensionType enum data.
 *
 * @mixin AllocationDimensionType
 */
class DimensionTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var AllocationDimensionType $this->resource */
        return [
            'type'                => $this->value,
            'label'               => $this->label(),
            'labelEN'             => $this->labelEN(),
            'isAlwaysVisible'     => $this->isAlwaysVisible(),
            'isConfigurable'      => $this->isConfigurable(),
            'defaultDisplayOrder' => $this->getDefaultDisplayOrder(),
        ];
    }
}
