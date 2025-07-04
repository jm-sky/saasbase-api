<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\PositionCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PositionCategory
 */
class PositionCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'isActive'    => $this->is_active,
            'sortOrder'   => $this->sort_order,
            'createdAt'   => $this->created_at->toIso8601String(),
        ];
    }
}
