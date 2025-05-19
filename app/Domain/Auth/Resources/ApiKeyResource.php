<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'lastUsedAt'   => $this->last_used_at,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
        ];
    }
}
