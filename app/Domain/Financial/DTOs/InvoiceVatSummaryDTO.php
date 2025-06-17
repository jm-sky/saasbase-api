<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Brick\Math\BigDecimal;

/**
 * @property VatRateDTO $vatRate
 * @property BigDecimal $net
 * @property BigDecimal $vat
 * @property BigDecimal $gross
 */
final class InvoiceVatSummaryDTO extends BaseDataDTO
{
    public function __construct(
        public VatRateDTO $vatRate,
        public BigDecimal $net,
        public BigDecimal $vat,
        public BigDecimal $gross,
    ) {
    }

    public function toArray(): array
    {
        return [
            'vatRate' => $this->vatRate->toArray(),
            'net'     => $this->net->toFloat(),
            'vat'     => $this->vat->toFloat(),
            'gross'   => $this->gross->toFloat(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            vatRate: VatRateDTO::fromArray($data['vatRate']),
            net: BigDecimal::of($data['net']),
            vat: BigDecimal::of($data['vat']),
            gross: BigDecimal::of($data['gross']),
        );
    }
}
