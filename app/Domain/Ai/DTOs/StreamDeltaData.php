<?php

namespace App\Domain\Ai\DTOs;

class StreamDeltaData
{
    public function __construct(
        public string $id,
        public int $index,
        public ?string $provider,
        public string $model,
        public string $content,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'index'    => $this->index,
            'provider' => $this->provider,
            'model'    => $this->model,
            'content'  => $this->content,
        ];
    }
}
