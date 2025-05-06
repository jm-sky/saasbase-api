<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Attachment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Attachment>
 *
 * @property string  $fileName
 * @property string  $fileUrl
 * @property string  $fileType
 * @property int     $fileSize
 * @property string  $attachmentableId
 * @property string  $attachmentableType
 * @property ?string $id                 UUID
 * @property ?Carbon $createdAt          Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt          Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt          Internally Carbon, accepts/serializes ISO 8601
 */
class AttachmentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $fileUrl,
        public readonly string $fileType,
        public readonly int $fileSize,
        public readonly string $attachmentableId,
        public readonly string $attachmentableType,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Attachment $model */
        return new static(
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

    public static function fromArray(array $data): static
    {
        return new static(
            fileName: $data['file_name'],
            fileUrl: $data['file_url'],
            fileType: $data['file_type'],
            fileSize: $data['file_size'],
            attachmentableId: $data['attachmentable_id'],
            attachmentableType: $data['attachmentable_type'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'fileName'           => $this->fileName,
            'fileUrl'            => $this->fileUrl,
            'fileType'           => $this->fileType,
            'fileSize'           => $this->fileSize,
            'attachmentableId'   => $this->attachmentableId,
            'attachmentableType' => $this->attachmentableType,
            'createdAt'          => $this->createdAt?->toIso8601String(),
            'updatedAt'          => $this->updatedAt?->toIso8601String(),
            'deletedAt'          => $this->deletedAt?->toIso8601String(),
        ];
    }
}
