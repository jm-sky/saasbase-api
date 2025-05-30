<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

class RecipientDto extends BaseDataDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $identifier = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'email'      => $this->email,
            'name'       => $this->name,
            'identifier' => $this->identifier,
        ];
    }
}

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

class SendMessageDto extends BaseDataDTO
{
    /**
     * @param RecipientDto[]  $recipients
     * @param AttachmentDto[] $attachments
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $content,
        public readonly array $recipients,
        public readonly array $attachments = [],
        public readonly ?string $refToMessageId = null,
        public readonly ?Carbon $createdAt = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'subject'        => $this->subject,
            'content'        => $this->content,
            'recipients'     => array_map(fn (RecipientDto $recipient) => $recipient->toArray(), $this->recipients),
            'attachments'    => array_map(fn (AttachmentDto $attachment) => $attachment->toArray(), $this->attachments),
            'refToMessageId' => $this->refToMessageId,
            'createdAt'      => $this->createdAt?->toIso8601String(),
        ];
    }
}
