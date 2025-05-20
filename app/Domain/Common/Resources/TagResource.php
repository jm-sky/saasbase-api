<?php

namespace App\Domain\Common\Resources;

use App\Domain\Common\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Tag $this->resource */
        return [
            'id'        => $this->id,
            'tenantId'  => $this->tenant_id,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
