<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Brick\Math\BigDecimal;

/**
 * @property string     $id
 * @property ?string    $description
 * @property BigDecimal $quantity
 * @property BigDecimal $unitPrice
 * @property VatRateDTO $vatRate
 * @property BigDecimal $totalNet
 * @property BigDecimal $totalVat
 * @property BigDecimal $totalGross
 * @property ?string    $productId
 */
final class InvoiceLineDTO extends BaseDataDTO
{
    public function __construct(
        public string $id,
        public ?string $description,
        public BigDecimal $quantity,
        public BigDecimal $unitPrice,
        public VatRateDTO $vatRate,
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
            'description' => $this->description ?? '',
            'quantity'    => $this->quantity->toFloat(),
            'unitPrice'   => $this->unitPrice->toFloat(),
            'vatRate'     => $this->vatRate->toArray(),
            'totalNet'    => $this->totalNet->toFloat(),
            'totalVat'    => $this->totalVat->toFloat(),
            'totalGross'  => $this->totalGross->toFloat(),
            'productId'   => $this->productId,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            description: $data['description'] ?? null,
            quantity: BigDecimal::of($data['quantity']),
            unitPrice: BigDecimal::of($data['unitPrice']),
            vatRate: VatRateDTO::fromArray($data['vatRate']),
            totalNet: BigDecimal::of($data['totalNet']),
            totalVat: BigDecimal::of($data['totalVat']),
            totalGross: BigDecimal::of($data['totalGross']),
            productId: $data['productId'] ?? null,
        );
    }
}
