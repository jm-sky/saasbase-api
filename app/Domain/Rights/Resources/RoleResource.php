<?php

namespace App\Domain\Rights\Resources;

use App\Domain\Rights\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string             $id
 * @property string             $name
 * @property ?string            $tenantId
 * @property array<int, string> $permissions
 */
class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Role $this */
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'tenantId'    => $this->tenant_id,
            'permissions' => $this->permissions->pluck('name'),
        ];
    }
}
