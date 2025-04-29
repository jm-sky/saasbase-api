<?php

namespace App\Services\ViesLookup\DTOs;

class ViesLookupResultDTO
{
    public function __construct(
        public readonly string $countryCode,
        public readonly string $vatNumber,
        public readonly bool $valid,
        public readonly ?string $name,
        public readonly ?string $address,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            countryCode: $data['countryCode'] ?? '',
            vatNumber: $data['vatNumber'] ?? '',
            valid: (bool) ($data['valid'] ?? false),
            name: $data['name'] ?? null,
            address: $data['address'] ?? null,
        );
    }
}
