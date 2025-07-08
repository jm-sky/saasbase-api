<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoiceLineDTO;
use App\Domain\Financial\DTOs\VatRateDTO;
use App\Domain\Financial\Models\VatRate;
use Brick\Math\BigDecimal;
use Illuminate\Support\Str;

class InvoiceLineDTOFactory extends DTOFactory
{
    public function make(?array $attributes = []): InvoiceLineDTO
    {
        if (!VatRate::exists()) {
            VatRate::factory()->count(3)->create();
        }

        $quantity  = $attributes['quantity'] ?? BigDecimal::of(fake()->randomFloat(2, 1, 10));
        $unitPrice = $attributes['unitPrice'] ?? BigDecimal::of(fake()->randomFloat(2, 10, 100));
        $vatRate   = $attributes['vatRate'] ?? VatRateDTO::fromModel(VatRate::inRandomOrder()->first());

        // Allow override of calculated values, but calculate them if not provided
        if (isset($attributes['totalNet'])) {
            $totalNet = $attributes['totalNet'];
        } else {
            $totalNet = $quantity->multipliedBy($unitPrice);
        }

        if (isset($attributes['totalVat'])) {
            $totalVat = $attributes['totalVat'];
        } else {
            $totalVat = $totalNet->multipliedBy($vatRate->rate / 100);
        }

        if (isset($attributes['totalGross'])) {
            $totalGross = $attributes['totalGross'];
        } else {
            $totalGross = $totalNet->plus($totalVat);
        }

        return new InvoiceLineDTO(
            id: $attributes['id'] ?? Str::ulid()->toString(),
            description: $attributes['description'] ?? fake()->sentence(),
            quantity: $quantity,
            unitPrice: $unitPrice,
            vatRate: $vatRate,
            totalNet: $totalNet,
            totalVat: $totalVat,
            totalGross: $totalGross,
            productId: $attributes['productId'] ?? null,
        );
    }
}
