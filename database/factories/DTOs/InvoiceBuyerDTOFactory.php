<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoiceBuyerDTO;

class InvoiceBuyerDTOFactory extends DTOFactory
{
    public function make(): InvoiceBuyerDTO
    {
        return new InvoiceBuyerDTO(
            contractorType: fake()->randomElement(['company', 'individual']),
            name: fake()->company(),
            address: fake()->address(),
            country: fake()->countryCode(),
            contractorId: null,
            taxId: fake()->numerify('##########'),
            iban: fake()->optional()->iban(),
            email: fake()->email(),
        );
    }
}
