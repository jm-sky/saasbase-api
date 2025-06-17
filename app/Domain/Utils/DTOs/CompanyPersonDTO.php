<?php

namespace App\Domain\Utils\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * @property ?string $name  Person's name
 * @property ?string $nip   NIP number (if applicable)
 * @property ?string $pesel PESEL number (if applicable)
 */
final class CompanyPersonDTO extends BaseDataDTO
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
