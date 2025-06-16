<?php

namespace Database\Factories\DTOs;

use App\Domain\Common\Models\VatRate;
use App\Domain\Financial\DTOs\InvoiceLineDTO;
use App\Domain\Financial\DTOs\VatRateDTO;
use Brick\Math\BigDecimal;
use Illuminate\Support\Str;

class InvoiceLineDTOFactory extends DTOFactory
{
    public function make(): InvoiceLineDTO
    {
        if (!VatRate::exists()) {
            VatRate::factory()->count(3)->create();
        }

        $quantity  = BigDecimal::of(fake()->randomFloat(2, 1, 10));
        $unitPrice = BigDecimal::of(fake()->randomFloat(2, 10, 100));
        $vatRate   = VatRateDTO::fromModel(VatRate::inRandomOrder()->first());

        $totalNet   = $quantity->multipliedBy($unitPrice);
        $totalVat   = $totalNet->multipliedBy($vatRate->rate / 100);
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
