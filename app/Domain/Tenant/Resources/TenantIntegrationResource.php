<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\TenantIntegration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var TenantIntegration $this */
        return [
            'id'           => $this->id,
            'tenantId'     => $this->tenant_id,
            'type'         => $this->type,
            'enabled'      => $this->enabled,
            'mode'         => $this->mode,
            'credentials'  => $this->credentials,
            'meta'         => $this->meta,
            'lastSyncedAt' => $this->last_synced_at?->toIso8601String(),
            'createdAt'    => $this->created_at->toIso8601String(),
            'updatedAt'    => $this->updated_at->toIso8601String(),
        ];
    }
}
