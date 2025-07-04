<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\Position;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Position
 */
class PositionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'isActive'    => $this->is_active,
            'isDirector'  => $this->is_director,
            'isLearning'  => $this->is_learning,
            'categoryId'  => $this->position_category_id,
            'category'    => $this->category->name,
            'createdAt'   => $this->created_at->toIso8601String(),
        ];
    }
}
