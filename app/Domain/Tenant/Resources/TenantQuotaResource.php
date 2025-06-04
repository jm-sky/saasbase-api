<?php

namespace App\Domain\Tenant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantQuotaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'storage' => [
                'used'      => $this->resource['storage']['used'],
                'total'     => $this->resource['storage']['total'],
                'unit'      => $this->resource['storage']['unit'],
            ],
            'users' => [
                'used'      => $this->resource['users']['used'],
                'total'     => $this->resource['users']['total'],
            ],
            'apiCalls' => [
                'used'      => $this->resource['apiCalls']['used'],
                'total'     => $this->resource['apiCalls']['total'],
            ],
        ];
    }
}
