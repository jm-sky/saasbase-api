<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Traits\HasMediaSignedUrls;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaDTO implements Arrayable, \JsonSerializable
{
    public const TEMPORARY_URL_EXPIRATION_TIME = 15;

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

    public static function fromModel(Media $media, ?Model $parent = null): static
    {
        if ($parent && $parent instanceof HasMediaSignedUrls) {
            $url = $parent->getMediaSignedUrl($media->collection_name, $media->file_name);
        }

        if ($parent) {
            $url = $parent->getMediaUrl($media->collection_name, $media->file_name);
        }

        return new static(
            id: $media->uuid ?? (string) $media->id,
            fileName: $media->file_name,
            fileUrl: $url ?? $media->getUrl(),
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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
