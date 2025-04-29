<?php

namespace App\Services\CompanyLookup\DTOs;

/**
 * Representative Data Transfer Object.
 */
class RepresentativeDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $nip,
        public readonly ?string $pesel,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            nip: $data['nip'] ?? null,
            pesel: $data['pesel'] ?? null,
        );
    }
}
