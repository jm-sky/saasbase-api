<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

final class StringField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        string $value
    ) {
        parent::__construct('string', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (string) ($data['value'] ?? '')
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (string) ($data['stringValue'] ?? $data['valueString'] ?? $data['value'] ?? '')
        );
    }

    public function validate(): void
    {
        if (!is_string($this->value)) {
            throw new \InvalidArgumentException('StringField value must be a string');
        }
    }
}
