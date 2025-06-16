<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

use App\Services\AzureDocumentIntelligence\Concerns\DocumentFieldInterface;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentFieldFactory;

class ObjectField extends ValueWrapper
{
    /**
     * @param array<string, DocumentFieldInterface> $fields
     */
    public function __construct(
        float $confidence,
        array $fields
    ) {
        parent::__construct('object', $confidence, $fields);
    }

    public static function fromArray(array $data): static
    {
        $fields = [];

        foreach (($data['value'] ?? []) as $key => $fieldData) {
            $fields[$key] = DocumentFieldFactory::fromArray($fieldData);
        }

        return new static(
            confidence: (float) ($data['confidence'] ?? 0),
            fields: $fields
        );
    }

    public function validate(): void
    {
        if (!is_array($this->value)) {
            throw new \InvalidArgumentException('ObjectField value must be an array');
        }

        foreach ($this->value as $field) {
            if (!$field instanceof DocumentFieldInterface) {
                throw new \InvalidArgumentException('ObjectField fields must implement DocumentFieldInterface');
            }
            $field->validate();
        }
    }

    /**
     * @return array<string, DocumentFieldInterface>
     */
    public function getFields(): array
    {
        return $this->value;
    }

    public function getField(string $key): ?DocumentFieldInterface
    {
        return $this->value[$key] ?? null;
    }
}
