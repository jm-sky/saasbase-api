<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoicePartyDTO;

class InvoicePartyDTOFactory extends DTOFactory
{
    public function make(?array $attributes = []): InvoicePartyDTO
    {
        return new InvoicePartyDTO(
            contractorType: $attributes['contractorType'] ?? fake()->randomElement(['company', 'individual']),
            name: $attributes['name'] ?? fake()->company(),
            address: $attributes['address'] ?? fake()->address(),
            country: $attributes['country'] ?? fake()->countryCode(),
            contractorId: $attributes['contractorId'] ?? null,
            taxId: $attributes['taxId'] ?? fake()->numerify('##########'),
            iban: $attributes['iban'] ?? fake()->optional()->iban(),
            email: $attributes['email'] ?? fake()->email(),
        );
    }
}
