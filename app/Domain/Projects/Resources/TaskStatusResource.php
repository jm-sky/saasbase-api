<?php

namespace App\Domain\Projects\Resources;

use App\Domain\Projects\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TaskStatus
 */
class TaskStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var TaskStatus $this->resource */
        return [
            'id'        => $this->id,
            'tenantId'  => $this->tenant_id,
            'name'      => $this->name,
            'color'     => $this->color,
            'sortOrder' => $this->sort_order,
            'isDefault' => $this->is_default,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
