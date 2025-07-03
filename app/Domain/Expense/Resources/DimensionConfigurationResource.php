<?php

namespace App\Domain\Expense\Resources;

use App\Domain\Expense\DTOs\DimensionConfigurationDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DimensionConfigurationDTO
 */
class DimensionConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var DimensionConfigurationDTO $this->resource */
        return [
            'dimensionType'        => $this->dimensionType->value,
            'label'                => $this->label,
            'labelEN'              => $this->labelEN,
            'isEnabled'            => $this->isEnabled,
            'isAlwaysVisible'      => $this->isAlwaysVisible,
            'isConfigurable'       => $this->isConfigurable,
            'displayOrder'         => $this->displayOrder,
            'defaultDisplayOrder'  => $this->defaultDisplayOrder,
        ];
    }
}
