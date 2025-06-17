<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

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

    public static function fromArray(array $data): static
    {
        return new self(
            subject: $data['subject'],
            content: $data['content'],
            recipients: array_map(fn (array $recipient) => RecipientDto::fromArray($recipient), $data['recipients']),
            attachments: array_map(fn (array $attachment) => AttachmentDto::fromArray($attachment), $data['attachments']),
            refToMessageId: $data['refToMessageId'],
            createdAt: $data['createdAt'],
        );
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
