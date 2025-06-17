<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoicePartyDTO;

class InvoicePartyDTOFactory extends DTOFactory
{
    public function make(): InvoicePartyDTO
    {
        return new InvoicePartyDTO(
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
