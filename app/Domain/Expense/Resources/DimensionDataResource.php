<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\DTOs\DimensionDataDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DimensionDataDTO
 */
class DimensionDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var DimensionDataDTO $this->resource */
        return [
            'dimensionType'   => $this->dimensionType->value,
            'label'           => $this->label,
            'labelEN'         => $this->labelEN,
            'isAlwaysVisible' => $this->isAlwaysVisible,
            'isConfigurable'  => $this->isConfigurable,
            'items'           => DimensionItemResource::collection($this->items),
        ];
    }
}
