<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class InvoiceBuyerDTO extends BaseDataDTO
{
    public function __construct(
        public int $contractorId,
        public string $contractorType,
        public string $name,
        public string $taxId,
        public string $address,
        public string $country,
        public ?string $iban = null,
        public string $email,
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

    public static function fromArray(array $data): self
    {
        return new self(
            contractorId: $data['contractorId'],
            contractorType: $data['contractorType'],
            name: $data['name'],
            taxId: $data['taxId'],
            address: $data['address'],
            country: $data['country'],
            iban: $data['iban'] ?? null,
            email: $data['email'],
        );
    }
}
