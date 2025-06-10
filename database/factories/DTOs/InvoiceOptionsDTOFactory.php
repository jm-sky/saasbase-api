<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoiceOptionsDTO;

class InvoiceOptionsDTOFactory extends DTOFactory
{
    public function make(): InvoiceOptionsDTO
    {
        return new InvoiceOptionsDTO(
            language: fake()->randomElement(['en', 'pl']),
            template: 'default',
            sendEmail: fake()->boolean(),
            emailTo: [fake()->email()],
        );
    }
}
