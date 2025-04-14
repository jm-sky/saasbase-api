<?php

namespace App\Domain\Common\DTOs;

use Carbon\Carbon;

class AttachmentDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $file_name,
        public readonly string $file_url,
        public readonly string $file_type,
        public readonly int $file_size,
        public readonly string $attachmentable_id,
        public readonly string $attachmentable_type,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
    ) {}

    public static function fromModel(\App\Domain\Common\Models\Attachment $model): self
    {
        return new self(
            id: $model->id,
            file_name: $model->file_name,
            file_url: $model->file_url,
            file_type: $model->file_type,
            file_size: $model->file_size,
            attachmentable_id: $model->attachmentable_id,
            attachmentable_type: $model->attachmentable_type,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'file_url' => $this->file_url,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'attachmentable_id' => $this->attachmentable_id,
            'attachmentable_type' => $this->attachmentable_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
