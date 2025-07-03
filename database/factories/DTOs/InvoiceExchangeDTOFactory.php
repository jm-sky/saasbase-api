<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoiceExchangeDTO;
use Brick\Math\BigDecimal;

class InvoiceExchangeDTOFactory extends DTOFactory
{
    protected $model = InvoiceExchangeDTO::class;

    public function make(?array $attributes = []): object
    {
        return new InvoiceExchangeDTO(
            currency: $attributes['currency'] ?? fake()->currencyCode(),
            exchangeRate: $attributes['exchangeRate'] ?? BigDecimal::of(fake()->randomFloat(6, 0.5, 1.5)),
            date: $attributes['date'] ?? fake()->date(),
        );
    }
}
