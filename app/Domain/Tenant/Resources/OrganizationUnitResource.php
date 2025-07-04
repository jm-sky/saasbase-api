<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\OrganizationUnit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrganizationUnit
 */
class OrganizationUnitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenant_id,
            'name'        => $this->name,
            'code'        => $this->code,
            'description' => $this->description,
            'isActive'    => $this->is_active,
            'isTechnical' => $this->is_technical,
            'parentId'    => $this->parent_id,
            'parent'      => new OrganizationUnitPreviewResource($this->parent),
            'users'       => OrganizationUnitUserResource::collection($this->activeUsers),
            'positions'   => PositionResource::collection($this->activePositions),
            'createdAt'   => $this->created_at->toIso8601String(),
            'updatedAt'   => $this->updated_at->toIso8601String(),
        ];
    }
}
