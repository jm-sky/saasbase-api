<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Brick\Math\BigDecimal;

/**
 * @property string     $currency
 * @property BigDecimal $exchangeRate
 * @property string     $date
 */
class InvoiceExchangeDTO extends BaseDataDTO
{
    public function __construct(
        public string $currency,
        public BigDecimal $exchangeRate,
        public string $date,
    ) {
    }

    public function toArray(): array
    {
        return [
            'currency'     => $this->currency,
            'exchangeRate' => $this->exchangeRate->toFloat(),
            'date'         => $this->date,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'],
            exchangeRate: new BigDecimal($data['exchangeRate'], 6),
            date: $data['date'],
        );
    }
}
