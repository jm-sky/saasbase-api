<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

class PhoneNumberField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        string $value
    ) {
        parent::__construct('phoneNumber', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (string) ($data['valuePhoneNumber'] ?? '')
        );
    }

    public function validate(): void
    {
        if (!is_string($this->value)) {
            throw new \InvalidArgumentException('PhoneNumberField value must be a string');
        }
    }
}
