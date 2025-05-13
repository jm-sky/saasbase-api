<?php

namespace App\Domain\Ai\DTOs;

class OpenRouterDeltaData
{
    public function __construct(
        public ?string $role = null,
        public ?string $content = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            role: $data['role'] ?? null,
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'role'    => $this->role,
            'content' => $this->content,
        ];
    }
}
