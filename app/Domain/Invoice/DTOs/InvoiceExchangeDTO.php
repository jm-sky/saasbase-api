<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Brick\Math\BigDecimal;

/**
 * @property string      $currency
 * @property ?BigDecimal $exchangeRate
 * @property ?string     $date
 */
class InvoiceExchangeDTO extends BaseDataDTO
{
    public function __construct(
        public string $currency,
        public ?BigDecimal $exchangeRate = null,
        public ?string $date = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'currency'     => $this->currency,
            'exchangeRate' => $this->exchangeRate?->toFloat(),
            'date'         => $this->date,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            currency: $data['currency'],
            exchangeRate: isset($data['exchangeRate']) ? BigDecimal::of($data['exchangeRate']) : null,
            date: isset($data['date']) ? $data['date'] : null,
        );
    }
}
