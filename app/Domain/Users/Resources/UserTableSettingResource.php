<?php

namespace App\Domain\Users\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTableSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'entity'    => $this->entity,
            'name'      => $this->name,
            'config'    => $this->config,
            'isDefault' => $this->is_default,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
