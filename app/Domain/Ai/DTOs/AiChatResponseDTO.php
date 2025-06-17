<?php

namespace App\Domain\Ai\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

final class AiChatResponseDTO extends BaseDataDTO
{
    public function __construct(
        public string $id,
        public string $content,
        public bool $streaming,
        public string $role,
        public bool $isAi,
        public Carbon $createdAt,
        public ?string $tempId = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            content: $data['content'],
            streaming: $data['streaming'],
            role: $data['role'],
            isAi: $data['isAi'],
            createdAt: $data['createdAt'],
            tempId: $data['tempId'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'tempId'    => $this->tempId,
            'content'   => $this->content,
            'streaming' => $this->streaming,
            'role'      => $this->role,
            'isAi'      => $this->isAi,
            'createdAt' => $this->createdAt->toIso8601String(),
        ];
    }
}
