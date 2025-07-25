<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

final class PhoneNumberField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        ?string $value
    ) {
        parent::__construct('phoneNumber', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: $data['confidence'] ?? 0,
            value: $data['value'] ?? null,
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            value: $data['valuePhoneNumber'] ?? null,
        );
    }

    public function validate(): void
    {
        if (!is_string($this->value)) {
            throw new \InvalidArgumentException('PhoneNumberField value must be a string');
        }
    }
}
