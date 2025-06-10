<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * DTO for a single document in Azure Document Intelligence response.
 *
 * @property string $docType
 * @property array  $fields
 * @property float  $confidence
 */
class Document extends BaseDataDTO
{
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

        $fields = self::sanitizeFields($data);

        return new static(
            docType: $data['docType'] ?? '',
            fields: $fields,
            confidence: (float) ($data['confidence'] ?? 0)
        );
    }

    protected static function sanitizeFields(array $data): array
    {
        $fields = [];

        foreach ($data['fields'] ?? [] as $key => $field) {
            if (is_array($field)) {
                echo "[Document] field: {$key}", PHP_EOL;
                $fields[$key] = DocumentField::fromArray($field);
            }
        }

        return $fields;
    }
}
