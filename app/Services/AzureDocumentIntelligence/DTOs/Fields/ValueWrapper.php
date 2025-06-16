<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

abstract class ValueWrapper
{
    public function __construct(
        public readonly string $type,
        public readonly float $confidence,
        public readonly mixed $value
    ) {
    }

    abstract public function validate(): void;

    public function toArray(): array
    {
        return [
            'type'       => $this->type,
            'confidence' => $this->confidence,
            'value'      => $this->value,
        ];
    }
}
