<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoicePaymentBankAccountDTO;

class InvoicePaymentBankAccountDTOFactory extends DTOFactory
{
    protected $model = InvoicePaymentBankAccountDTO::class;

    public function make(?array $attributes = []): InvoicePaymentBankAccountDTO
    {
        return new InvoicePaymentBankAccountDTO(
            iban: $attributes['iban'] ?? fake()->iban(),
            country: $attributes['country'] ?? fake()->countryCode(),
            swift: $attributes['swift'] ?? fake()->text(11),
            bankName: $attributes['bankName'] ?? fake()->company(),
        );
    }
}
