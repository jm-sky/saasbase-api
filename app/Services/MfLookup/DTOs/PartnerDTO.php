<?php

namespace App\Services\MfLookup\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Partner Data Transfer Object.
 */
final class PartnerDTO extends BaseDataDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $nip,
        public readonly ?string $pesel,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'] ?? null,
            nip: $data['nip'] ?? null,
            pesel: $data['pesel'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'  => $this->name,
            'nip'   => $this->nip,
            'pesel' => $this->pesel,
        ];
    }
}
