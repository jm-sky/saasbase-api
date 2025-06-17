<?php

namespace App\Domain\Users\Resources;

use App\Domain\Users\Models\TrustedDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TrustedDevice
 */
class TrustedDeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'deviceName'   => $this->device_name,
            'browser'      => $this->browser,
            'os'           => $this->os,
            'location'     => $this->location,
            'lastActiveAt' => $this->last_active_at,
            'ipAddress'    => $this->ip_address,
            'trustedUntil' => $this->trusted_until,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
        ];
    }
}
