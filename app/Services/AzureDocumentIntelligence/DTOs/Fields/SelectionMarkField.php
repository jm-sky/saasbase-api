<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

final class SelectionMarkField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        bool $value
    ) {
        parent::__construct('selectionMark', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            value: (bool) ($data['valueSelectionMark'] ?? false)
        );
    }

    public function validate(): void
    {
        if (!is_bool($this->value)) {
            throw new \InvalidArgumentException('SelectionMarkField value must be a boolean');
        }
    }
}
