<?php

namespace Tests\Support;

use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use Database\Factories\DTOs\InvoiceBodyDTOFactory;
use Database\Factories\DTOs\InvoiceOptionsDTOFactory;
use Database\Factories\DTOs\InvoicePartyDTOFactory;
use Database\Factories\DTOs\InvoicePaymentDTOFactory;

class ExpenseApiPayloadFactory
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function make(array $overrides = []): array
    {
        return array_merge([
            'type' => InvoiceType::Basic->value,
            'status' => InvoiceStatus::DRAFT->value,
            'issueDate' => now()->toDateString(),
            'number' => 'EXP-TEST-001',
            'totalNet' => 100.00,
            'totalTax' => 23.00,
            'totalGross' => 123.00,
            'currency' => 'PLN',
            'exchangeRate' => 1,
            'seller' => (new InvoicePartyDTOFactory)->make()->toArray(),
            'buyer' => (new InvoicePartyDTOFactory)->make()->toArray(),
            'body' => (new InvoiceBodyDTOFactory)->make()->toArray(),
            'payment' => (new InvoicePaymentDTOFactory)->make()->toArray(),
            'options' => (new InvoiceOptionsDTOFactory)->make()->toArray(),
        ], $overrides);
    }
}
