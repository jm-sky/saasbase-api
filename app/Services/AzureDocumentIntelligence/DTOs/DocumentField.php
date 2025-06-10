<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * DTO for a field in a document in Azure Document Intelligence response.
 *
 * @property string $type
 * @property mixed  $value
 * @property float  $confidence
 */
class DocumentField extends BaseDataDTO
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $value,
        public readonly float $confidence
    ) {
    }

    public function toArray(): array
    {
        return [
            'type'       => $this->type,
            'value'      => $this->value,
            'confidence' => $this->confidence,
        ];
    }

    public static function fromArray(array $data): static
    {
        unset($data['boundingRegions']);
        $value = self::getValueFromRawData($data);

        if (null === $value && isset($data['content'])) {
            $value = $data['content'];
        }

        return new static(
            type: $data['type'] ?? '',
            value: $value,
            confidence: (float) ($data['confidence'] ?? 0)
        );
    }

    public static function getValueFromRawData(array $data): mixed
    {
        return match ($data['type'] ?? '') {
            'currency' => $data['valueCurrency']['amount'] ?? null,
            'date'     => $data['valueDate'] ?? null,
            'number'   => $data['valueNumber'] ?? null,
            'string'   => $data['valueString'] ?? null,
            'array'    => $data['valueArray'] ?? [],
            'object'   => $data['valueObject'] ?? null,
            default    => null,
        };
    }
}
