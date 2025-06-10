<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoiceLineDTO;
use App\Domain\Invoice\Enums\VatRate;
use Brick\Math\BigDecimal;
use Illuminate\Support\Str;

class InvoiceLineDTOFactory extends DTOFactory
{
    public function make(): InvoiceLineDTO
    {
        $quantity  = BigDecimal::of(fake()->randomFloat(2, 1, 10));
        $unitPrice = BigDecimal::of(fake()->randomFloat(2, 10, 100));
        $vatRate   = fake()->randomElement(VatRate::cases());

        $totalNet   = $quantity->multipliedBy($unitPrice);
        $totalVat   = $totalNet->multipliedBy($vatRate->rate() / 100);
        $totalGross = $totalNet->plus($totalVat);

        return new InvoiceLineDTO(
            id: Str::ulid()->toString(),
            description: fake()->sentence(),
            quantity: $quantity,
            unitPrice: $unitPrice,
            vatRate: $vatRate,
            totalNet: $totalNet,
            totalVat: $totalVat,
            totalGross: $totalGross,
            productId: null,
        );
    }
}
