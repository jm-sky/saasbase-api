<?php

namespace App\Services\AzureDocumentIntelligence\DTOs;

use App\Services\AzureDocumentIntelligence\DTOs\Fields\AddressField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\ArrayField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\CurrencyField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\DateField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\NumberField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\ObjectField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\StringField;
use App\Services\AzureDocumentIntelligence\DTOs\Fields\ValueWrapper;

class DocumentFieldFactory
{
    public static function fromArray(array $data): ValueWrapper
    {
        return match ($data['type'] ?? null) {
            'string'   => StringField::fromArray($data),
            'currency' => CurrencyField::fromArray($data),
            'date'     => DateField::fromArray($data),
            'number'   => NumberField::fromArray($data),
            'address'  => AddressField::fromArray($data),
            'object'   => ObjectField::fromArray($data),
            'array'    => ArrayField::fromArray($data),
            default    => throw new \InvalidArgumentException('Unknown document field type: ' . ($data['type'] ?? 'null')),
        };
    }

    public static function tryFromArray(array $data): ?ValueWrapper
    {
        if (empty($data)) {
            return null;
        }

        return self::fromArray($data);
    }
}
