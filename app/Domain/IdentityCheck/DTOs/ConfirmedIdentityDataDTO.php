<?php

namespace App\Domain\IdentityCheck\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class ConfirmedIdentityDataDTO extends BaseDataDTO
{
    public function __construct(
        public ?string $fullName = null,
        public ?string $pesel = null,
        public ?string $birthDate = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'fullName'  => $this->fullName,
            'pesel'     => $this->pesel,
            'birthDate' => $this->birthDate,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['fullName'] ?? null,
            $data['pesel'] ?? null,
            $data['birthDate'] ?? null,
        );
    }
}
