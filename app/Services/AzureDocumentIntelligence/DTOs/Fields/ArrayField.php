<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

use App\Services\AzureDocumentIntelligence\Concerns\DocumentFieldInterface;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentFieldFactory;

class ArrayField extends ValueWrapper
{
    /**
     * @param DocumentFieldInterface[] $items
     */
    public function __construct(
        float $confidence,
        array $items
    ) {
        parent::__construct('array', $confidence, $items);
    }

    public static function fromArray(array $data): static
    {
        $items = [];

        foreach (($data['value'] ?? []) as $itemData) {
            $items[] = DocumentFieldFactory::fromArray($itemData);
        }

        return new static(
            confidence: (float) ($data['confidence'] ?? 0),
            items: $items
        );
    }

    public function validate(): void
    {
        if (!is_array($this->value)) {
            throw new \InvalidArgumentException('ArrayField value must be an array');
        }

        foreach ($this->value as $item) {
            if (!$item instanceof DocumentFieldInterface) {
                throw new \InvalidArgumentException('ArrayField items must implement DocumentFieldInterface');
            }
            $item->validate();
        }
    }

    /**
     * @return DocumentFieldInterface[]
     */
    public function getItems(): array
    {
        return $this->value;
    }
}
