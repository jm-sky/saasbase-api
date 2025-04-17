<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Attachment;
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

    public static function fromModel(Attachment $model): self
    {
        return new self(
            fileName: $model->file_name,
            fileUrl: $model->file_url,
            fileType: $model->file_type,
            fileSize: $model->file_size,
            attachmentableId: $model->attachmentable_id,
            attachmentableType: $model->attachmentable_type,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
