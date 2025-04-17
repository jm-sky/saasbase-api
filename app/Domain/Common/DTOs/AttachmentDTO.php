<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Attachment;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $fileName
 * @property string $fileUrl
 * @property string $fileType
 * @property int $fileSize
 * @property string $attachmentableId
 * @property string $attachmentableType
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
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
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
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
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
