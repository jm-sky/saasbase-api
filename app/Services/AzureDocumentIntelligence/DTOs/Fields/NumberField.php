<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

final class NumberField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        float|int $value
    ) {
        parent::__construct('number', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: $data['confidence'] ?? 0,
            value: $data['value'] ?? 0,
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (float) ($data['value'] ?? 0)
        );
    }

    public function validate(): void
    {
        if (!is_numeric($this->value)) {
            throw new \InvalidArgumentException('NumberField value must be numeric');
        }
    }
}
