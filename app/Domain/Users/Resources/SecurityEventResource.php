<?php

namespace App\Domain\Users\Resources;

use App\Domain\Users\Models\SecurityEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SecurityEvent
 */
class SecurityEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'eventType' => $this->event_type,
            'ipAddress' => $this->ip_address,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
