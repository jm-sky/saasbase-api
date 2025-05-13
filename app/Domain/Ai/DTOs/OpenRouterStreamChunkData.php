<?php

namespace App\Domain\Ai\DTOs;

class OpenRouterStreamChunkData
{
    public function __construct(
        public string $id,
        public ?string $provider,
        public string $model,
        public string $object,
        public int $created,
        /** @var array<OpenRouterChoiceData> */
        public array $choices,
        public $systemFingerprint = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            provider: $data['provider'],
            model: $data['model'],
            object: $data['object'],
            created: $data['created'],
            choices: is_array($data['choices']) ? array_map(fn (array $choice) =>OpenRouterChoiceData::fromArray($choice), $data['choices']) : [],
            systemFingerprint: $data['systemFingerprint'] ?? null,
        );
    }
}
