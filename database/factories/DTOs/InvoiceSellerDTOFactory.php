<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoiceSellerDTO;

class InvoiceSellerDTOFactory extends DTOFactory
{
    public function make(): InvoiceSellerDTO
    {
        return new InvoiceSellerDTO(
            contractorType: 'company',
            name: fake()->company(),
            address: fake()->address(),
            country: fake()->countryCode(),
            taxId: fake()->numerify('##########'),
            iban: fake()->iban(),
            contractorId: null,
            email: fake()->companyEmail(),
        );
    }
}
