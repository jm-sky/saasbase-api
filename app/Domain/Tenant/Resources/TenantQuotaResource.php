<?php

namespace App\Domain\Tenant\Resources;

use App\Domain\Tenant\DTOs\TenantQuotaDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantQuotaDTO
 */
class TenantQuotaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var TenantQuotaDTO $this */
        return [
            'storage'  => $this->storage->toArray(),
            'users'    => $this->users->toArray(),
            'apiCalls' => $this->apiCalls->toArray(),
        ];
    }
}
