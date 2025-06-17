<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

class SendResultDto extends BaseDataDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $messageId = null,
        public readonly ?string $error = null,
        public readonly ?Carbon $sentAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            success: $data['success'],
            messageId: $data['messageId'],
            error: $data['error'],
            sentAt: $data['sentAt'],
        );
    }

    public function toArray(): array
    {
        return [
            'success'   => $this->success,
            'messageId' => $this->messageId,
            'error'     => $this->error,
            'sentAt'    => $this->sentAt?->toIso8601String(),
        ];
    }
}
