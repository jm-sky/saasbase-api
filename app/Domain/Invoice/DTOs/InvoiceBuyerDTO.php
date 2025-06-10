<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class InvoiceBuyerDTO extends BaseDataDTO
{
    public function __construct(
        public string $contractorType,
        public string $name,
        public string $address,
        public string $country,
        public ?string $contractorId = null,
        public ?string $taxId = null,
        public ?string $iban = null,
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
        return new static(
            contractorId: $data['contractorId'] ?? null,
            contractorType: $data['contractorType'],
            name: $data['name'],
            taxId: $data['taxId'] ?? null,
            address: $data['address'],
            country: $data['country'],
            iban: $data['iban'] ?? null,
            email: $data['email'] ?? null,
        );
    }
}
