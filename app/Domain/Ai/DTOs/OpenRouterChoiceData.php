<?php

namespace App\Domain\Ai\DTOs;

class OpenRouterChoiceData
{
    public function __construct(
        public int $index,
        public OpenRouterDeltaData $delta,
        public $finishReason = null,
        public $nativeFinishReason = null,
        public $logprobs = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            index: $data['index'],
            delta: OpenRouterDeltaData::fromArray($data['delta']),
            finishReason: $data['finish_reason'] ?? null,
            nativeFinishReason: $data['native_finish_reason'] ?? null,
            logprobs: $data['logprobs'] ?? null,
        );
    }
}
