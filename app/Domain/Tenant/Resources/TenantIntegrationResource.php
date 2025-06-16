<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\Models\TenantIntegration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'credentials'  => $this->camelizeObjectKeys($this->credentials),
            'meta'         => $this->meta,
            'lastSyncedAt' => $this->last_synced_at?->toIso8601String(),
            'createdAt'    => $this->created_at->toIso8601String(),
            'updatedAt'    => $this->updated_at->toIso8601String(),
        ];
    }

    protected function camelizeObjectKeys(array $object): array
    {
        return collect($object)->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        })->toArray();
    }
}
