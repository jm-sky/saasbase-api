<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Invoice\Enums\VatRate;
use Brick\Math\BigDecimal;

/**
 * @property VatRate    $vatRate
 * @property BigDecimal $net
 * @property BigDecimal $vat
 * @property BigDecimal $gross
 */
class InvoiceVatSummaryDTO extends BaseDataDTO
{
    public function __construct(
        public VatRate $vatRate,
        public BigDecimal $net,
        public BigDecimal $vat,
        public BigDecimal $gross,
    ) {
    }

    public function toArray(): array
    {
        return [
            'vatRate' => $this->vatRate->value,
            'net'     => $this->net->toFloat(),
            'vat'     => $this->vat->toFloat(),
            'gross'   => $this->gross->toFloat(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            vatRate: VatRate::from($data['vatRate']),
            net: new BigDecimal($data['net']),
            vat: new BigDecimal($data['vat']),
            gross: new BigDecimal($data['gross']),
        );
    }
}
