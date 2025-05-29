<?php

namespace App\Domain\Utils\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistryConfirmationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'payload'   => $this->payload,
            'result'    => $this->result,
            'success'   => $this->success,
            'checkedAt' => $this->checked_at,
        ];
    }
}
