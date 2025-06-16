<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

class StringField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        string $value
    ) {
        parent::__construct('string', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (string) ($data['stringValue'] ?? '')
        );
    }

    public function validate(): void
    {
        if (!is_string($this->value)) {
            throw new \InvalidArgumentException('StringField value must be a string');
        }
    }
}
