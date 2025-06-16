<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

use Carbon\Carbon;

class DateField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        Carbon $value
    ) {
        parent::__construct('date', $confidence, $value);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            confidence: (float) ($data['confidence'] ?? 0),
            value: Carbon::parse($data['value'] ?? ''),
        );
    }

    public function validate(): void
    {
        if (!$this->value instanceof Carbon) {
            throw new \InvalidArgumentException('DateField value must be a Carbon instance');
        }
    }

    public function toArray(): array
    {
        return [
            'type'       => $this->type,
            'confidence' => $this->confidence,
            'value'      => $this->value->toIso8601String(),
        ];
    }
}
