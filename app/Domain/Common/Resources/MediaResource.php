<?php

namespace App\Domain\Common\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* @var Media $this->resource */
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'fileName'  => $this->file_name,
            'mimeType'  => $this->mime_type,
            'size'      => $this->size,
            'url'       => $this->getUrl(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
