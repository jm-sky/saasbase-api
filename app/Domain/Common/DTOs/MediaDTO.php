<?php

namespace App\Domain\Common\DTOs;

use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $fileName,
        public readonly string $fileUrl,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly ?string $collectionName = null,
        public readonly ?Carbon $createdAt = null,
        public readonly ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Media $media): static
    {
        return new static(
            id: $media->uuid ?? (string) $media->id,
            fileName: $media->file_name,
            fileUrl: $media->getFullUrl(),
            mimeType: $media->mime_type,
            size: $media->size,
            collectionName: $media->collection_name,
            createdAt: $media->created_at,
            updatedAt: $media->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'fileName'       => $this->fileName,
            'fileUrl'        => $this->fileUrl,
            'mimeType'       => $this->mimeType,
            'size'           => $this->size,
            'collectionName' => $this->collectionName,
            'createdAt'      => $this->createdAt?->toIso8601String(),
            'updatedAt'      => $this->updatedAt?->toIso8601String(),
        ];
    }

    /**
     * Helper for collections.
     */
    public static function collection($mediaItems): array
    {
        return collect($mediaItems)->map(fn ($media) => static::fromModel($media)->toArray())->all();
    }
}
