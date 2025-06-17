<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Services\AzureDocumentIntelligence\Concerns\DocumentFieldInterface;

/**
 * DTO for a single document in Azure Document Intelligence response.
 *
 * @property string                                $docType
 * @property array<string, DocumentFieldInterface> $fields
 * @property float                                 $confidence
 */
final class Document extends BaseDataDTO
{
    /**
     * @param array<string, DocumentFieldInterface> $fields
     */
    public function __construct(
        public readonly string $docType,
        public readonly array $fields,
        public readonly float $confidence
    ) {
    }

    public function toArray(): array
    {
        return [
            'docType'    => $this->docType,
            'fields'     => array_map(fn ($field) => $field->toArray(), $this->fields),
            'confidence' => $this->confidence,
        ];
    }

    public static function fromArray(array $data): static
    {
        unset($data['boundingRegions']);

        $fields = [];

        foreach (($data['fields'] ?? []) as $key => $fieldData) {
            $fields[$key] = DocumentFieldFactory::fromArray($fieldData);
        }

        return new self(
            docType: $data['docType'] ?? '',
            fields: $fields,
            confidence: (float) ($data['confidence'] ?? 0)
        );
    }
}
