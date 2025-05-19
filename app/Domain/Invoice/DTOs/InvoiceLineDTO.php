<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\Invoice\Enums\VatRate;
use Brick\Math\BigDecimal;

/**
 * @property string     $id
 * @property string     $description
 * @property BigDecimal $quantity
 * @property BigDecimal $unitPrice
 * @property VatRate    $vatRate
 * @property BigDecimal $totalNet
 * @property BigDecimal $totalVat
 * @property BigDecimal $totalGross
 * @property ?string    $productId
 */
class InvoiceLineDTO extends BaseDataDTO
{
    public function __construct(
        public string $id,
        public string $description,
        public BigDecimal $quantity,
        public BigDecimal $unitPrice,
        public VatRate $vatRate,
        public BigDecimal $totalNet,
        public BigDecimal $totalVat,
        public BigDecimal $totalGross,
        public ?string $productId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'description' => $this->description,
            'quantity'    => $this->quantity->toFloat(),
            'unitPrice'   => $this->unitPrice->toFloat(),
            'vatRate'     => $this->vatRate->value,
            'totalNet'    => $this->totalNet->toFloat(),
            'totalVat'    => $this->totalVat->toFloat(),
            'totalGross'  => $this->totalGross->toFloat(),
            'productId'   => $this->productId,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            description: $data['description'],
            quantity: new BigDecimal($data['quantity']),
            unitPrice: new BigDecimal($data['unitPrice']),
            vatRate: VatRate::from($data['vatRate']),
            totalNet: new BigDecimal($data['totalNet']),
            totalVat: new BigDecimal($data['totalVat']),
            totalGross: new BigDecimal($data['totalGross']),
            productId: $data['productId'] ?? null,
        );
    }
}
