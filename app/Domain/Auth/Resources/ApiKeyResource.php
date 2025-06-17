<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApiKey
 */
class ApiKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'scopes'       => $this->scopes,
            'key'          => $this->key,
            'isActive'     => $this->is_active,
            'lastUsedAt'   => $this->last_used_at,
            'expiresAt'    => $this->expires_at,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
        ];
    }
}
