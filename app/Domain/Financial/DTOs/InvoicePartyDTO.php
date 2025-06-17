<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class InvoicePartyDTO extends BaseDataDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $address = null,
        public ?string $country = null,
        public ?string $taxId = null,
        public ?string $iban = null,
        public ?string $contractorId = null,
        public ?string $contractorType = null,
        public ?string $email = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'contractorId'   => $this->contractorId,
            'contractorType' => $this->contractorType,
            'name'           => $this->name,
            'taxId'          => $this->taxId,
            'address'        => $this->address,
            'country'        => $this->country,
            'iban'           => $this->iban,
            'email'          => $this->email,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            contractorId: $data['contractorId'] ?? null,
            contractorType: $data['contractorType'] ?? null,
            name: $data['name'] ?? null,
            taxId: $data['taxId'] ?? null,
            address: $data['address'] ?? null,
            country: $data['country'] ?? null,
            iban: $data['iban'] ?? null,
            email: $data['email'] ?? null,
        );
    }
}
