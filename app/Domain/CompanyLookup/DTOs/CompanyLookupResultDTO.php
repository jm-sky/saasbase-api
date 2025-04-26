<?php

namespace App\Domain\CompanyLookup\DTOs;

class CompanyLookupResultDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $nip,
        public readonly ?string $regon,
        public readonly ?string $address,
        public readonly ?string $vatStatus,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            nip: $data['nip'] ?? '',
            regon: $data['regon'] ?? null,
            address: $data['workingAddress'] ?? null,
            vatStatus: $data['statusVat'] ?? null,
        );
    }
}
