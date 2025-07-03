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
            'name'        => $this->name,
            'code'        => $this->code,
            'description' => $this->description,
            'isActive'    => $this->is_active,
            'parentId'    => $this->parent_id,
            'users'       => OrganizationUnitUserResource::collection($this->users),
            'createdAt'   => $this->created_at->toIso8601String(),
            'updatedAt'   => $this->updated_at->toIso8601String(),
        ];
    }
}
