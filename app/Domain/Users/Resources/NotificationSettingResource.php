<?php

namespace App\Domain\Users\Resources;

use App\Domain\Users\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NotificationSetting
 */
class NotificationSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'channel'    => $this->channel,
            'settingKey' => $this->setting_key,
            'enabled'    => $this->enabled,
            'createdAt'  => $this->created_at,
            'updatedAt'  => $this->updated_at,
        ];
    }
}
