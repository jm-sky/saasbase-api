<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

class EmailField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        string $value
    ) {
        parent::__construct('email', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (string) ($data['valueEmail'] ?? '')
        );
    }

    public function validate(): void
    {
        if (!is_string($this->value)) {
            throw new \InvalidArgumentException('EmailField value must be a string');
        }

        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('EmailField value must be a valid email address');
        }
    }
}
