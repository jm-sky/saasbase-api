<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

use Carbon\Carbon;

final class TimeField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        Carbon $value
    ) {
        parent::__construct('time', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: $data['confidence'] ?? 0,
            value: Carbon::parse($data['value'] ?? ''),
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            value: Carbon::parse($data['valueTime'] ?? '')
        );
    }

    public function validate(): void
    {
        if (!$this->value instanceof Carbon) {
            throw new \InvalidArgumentException('TimeField value must be a Carbon instance');
        }
    }

    public function toArray(): array
    {
        return [
            'type'       => $this->type,
            'confidence' => $this->confidence,
            'value'      => $this->value->format('H:i:s'),
        ];
    }
}
