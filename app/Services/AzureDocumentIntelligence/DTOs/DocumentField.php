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
            'value'      => $this->value instanceof BaseDataDTO ? $this->value->toArray() : $this->value,
            'confidence' => $this->confidence,
        ];
    }

    public static function fromArray(array $data): static
    {
        unset($data['boundingRegions']);

        $value = self::getValueFromRawData($data);

        echo "[DocumentField] type: {$data['type']}", PHP_EOL;
        echo '[DocumentField] value: ' . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;

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
            'array'    => self::parseValueArray($data['valueArray'] ?? null),
            'object'   => self::parseValueObject($data['valueObject'] ?? null),
            default    => null,
        };
    }

    protected static function parseValueArray(array $valueArray): array
    {
        $result = [];

        foreach ($valueArray as $item) {
            if (is_array($item)) {
                $result[] = self::fromArray($item);
            }
        }

        return $result;
    }

    protected static function parseValueObject(array $valueObject): object
    {
        $result = new \stdClass();

        foreach ($valueObject as $key => $field) {
            if (is_array($field)) {
                $result->{$key} = self::fromArray($field);
            }
        }

        return $result;
    }
}
