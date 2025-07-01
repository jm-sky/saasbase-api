<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

final class SyncResultDto extends BaseDataDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly int $newMessages = 0,
        public readonly int $updatedMessages = 0,
        public readonly ?string $error = null,
        public readonly ?Carbon $syncedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            success: $data['success'],
            newMessages: $data['newMessages'],
            updatedMessages: $data['updatedMessages'],
            error: $data['error'],
            syncedAt: $data['syncedAt'],
        );
    }

    public function toArray(): array
    {
        return [
            'success'         => $this->success,
            'newMessages'     => $this->newMessages,
            'updatedMessages' => $this->updatedMessages,
            'error'           => $this->error,
            'syncedAt'        => $this->syncedAt?->toIso8601String(),
        ];
    }
}
