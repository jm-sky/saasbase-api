<?php

namespace App\Domain\Common\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $fileName
 * @property string $fileUrl
 * @property string $fileType
 * @property int $fileSize
 * @property string $attachmentableId
 * @property string $attachmentableType
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class AttachmentDTO extends Data
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $fileUrl,
        public readonly string $fileType,
        public readonly int $fileSize,
        public readonly string $attachmentableId,
        public readonly string $attachmentableType,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
