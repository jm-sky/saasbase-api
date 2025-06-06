<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IdentityConfirmationTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /* @var Media $this->resource */
        return [
            'id'        => $this->id,
            'fileName'  => $this->file_name,
            'mimeType'  => $this->mime_type,
            'size'      => $this->size,
            'url'       => $this->getFullUrl(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
