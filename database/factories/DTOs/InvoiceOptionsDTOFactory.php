<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoiceOptionsDTO;

class InvoiceOptionsDTOFactory extends DTOFactory
{
    public function make(?array $attributes = []): InvoiceOptionsDTO
    {
        return new InvoiceOptionsDTO(
            language: $attributes['language'] ?? fake()->randomElement(config('app.supported_locales')),
            template: $attributes['template'] ?? 'default',
            sendEmail: $attributes['sendEmail'] ?? fake()->boolean(),
            emailTo: $attributes['emailTo'] ?? [fake()->email()],
        );
    }
}
