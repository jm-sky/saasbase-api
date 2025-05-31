<?php

namespace App\Domain\EDoreczenia\Resources;

use App\Domain\Common\Resources\MediaResource;
use App\Domain\Common\Resources\UserPreviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EDoreczeniaMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenant_id,
            'userId'      => $this->user_id,
            'provider'    => $this->provider,
            'recipient'   => $this->recipient,
            'subject'     => $this->subject,
            'content'     => $this->content,
            'status'      => $this->status,
            'externalId'  => $this->external_id,
            'createdAt'   => $this->created_at,
            'updatedAt'   => $this->updated_at,
            'creator'     => new UserPreviewResource($this->whenLoaded('creator')),
            'attachments' => MediaResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
