<?php

namespace App\Domain\Template\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Template DTO for party information (seller/buyer) in invoice templates.
 */
final class InvoicePartyTemplateDTO extends BaseDataDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $taxId = null,
        public readonly ?string $address = null,
        public readonly ?string $country = null,
        public readonly ?string $iban = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'] ?? null,
            taxId: $data['taxId'] ?? null,
            address: $data['address'] ?? null,
            country: $data['country'] ?? null,
            iban: $data['iban'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'    => $this->name,
            'taxId'   => $this->taxId,
            'address' => $this->address,
            'country' => $this->country,
            'iban'    => $this->iban,
            'email'   => $this->email,
            'phone'   => $this->phone,
        ];
    }
}
