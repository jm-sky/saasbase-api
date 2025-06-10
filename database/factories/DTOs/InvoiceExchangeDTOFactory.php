<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoiceExchangeDTO;
use Brick\Math\BigDecimal;

class InvoiceExchangeDTOFactory extends DTOFactory
{
    protected $model = InvoiceExchangeDTO::class;

    public function make(): object
    {
        return new InvoiceExchangeDTO(
            currency: fake()->currencyCode(),
            exchangeRate: BigDecimal::of(fake()->randomFloat(6, 0.5, 1.5)),
            date: fake()->date(),
        );
    }
}
