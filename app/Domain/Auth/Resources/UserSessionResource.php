<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserSession
 */
class UserSessionResource extends JsonResource
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
            'type'         => $this->type->value,
            'deviceName'   => $this->device_name,
            'ipAddress'    => $this->ip_address,
            'lastActiveAt' => $this->last_active_at->toIso8601String(),
            'expiresAt'    => $this->expires_at?->toIso8601String(),
            'isCurrent'    => $this->isCurrent(),
            'isActive'     => $this->isActive(),
        ];
    }
}
