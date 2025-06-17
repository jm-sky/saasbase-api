<?php

namespace App\Services\AzureDocumentIntelligence\DTOs\Fields;

use Brick\Math\BigDecimal;

final class CurrencyField extends ValueWrapper
{
    public function __construct(
        float $confidence,
        ?BigDecimal $amount,
        ?string $currencyCode
    ) {
        parent::__construct('currency', $confidence, [
            'amount'       => $amount,
            'currencyCode' => $currencyCode,
        ]);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            confidence: $data['confidence'] ?? 0,
            amount: isset($data['amount']) ? BigDecimal::of($data['amount']) : null,
            currencyCode: $data['currencyCode'] ?? null,
        );
    }

    public static function fromAzureArray(array $data): static
    {
        return new self(
            confidence: (float) ($data['confidence'] ?? 0),
            amount: BigDecimal::of($data['valueCurrency']['amount'] ?? 0),
            currencyCode: (string) ($data['valueCurrency']['currencyCode'] ?? '')
        );
    }

    public function toArray(): array
    {
        return [
            'type'         => $this->type,
            'confidence'   => $this->confidence,
            'amount'       => $this->getAmount()->toFloat(),
            'currencyCode' => $this->getCurrencyCode(),
        ];
    }

    public function validate(): void
    {
        if (!is_array($this->value)
            || !isset($this->value['amount'])
            || !isset($this->value['currencyCode'])) {
            throw new \InvalidArgumentException('CurrencyField value must have amount and currencyCode');
        }

        if (!$this->value['amount'] instanceof BigDecimal) {
            throw new \InvalidArgumentException('CurrencyField amount must be numeric');
        }

        if (!is_string($this->value['currencyCode'])) {
            throw new \InvalidArgumentException('CurrencyField currencyCode must be a string');
        }
    }

    public function getAmount(): BigDecimal
    {
        return $this->value['amount'];
    }

    public function getCurrencyCode(): string
    {
        return $this->value['currencyCode'];
    }
}
