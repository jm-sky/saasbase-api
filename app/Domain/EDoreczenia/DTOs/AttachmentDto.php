<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

class AttachmentDto extends BaseDataDTO
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $filePath,
        public readonly int $fileSize,
        public readonly string $mimeType,
        public readonly ?Carbon $createdAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            fileName: $data['fileName'],
            filePath: $data['filePath'],
            fileSize: $data['fileSize'],
            mimeType: $data['mimeType'],
            createdAt: $data['createdAt'],
        );
    }

    public function toArray(): array
    {
        return [
            'fileName'  => $this->fileName,
            'filePath'  => $this->filePath,
            'fileSize'  => $this->fileSize,
            'mimeType'  => $this->mimeType,
            'createdAt' => $this->createdAt?->toIso8601String(),
        ];
    }
}
