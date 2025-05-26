<?php

namespace App\Domain\Utils\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * @property ?string $name  Person's name
 * @property ?string $nip   NIP number (if applicable)
 * @property ?string $pesel PESEL number (if applicable)
 */
class CompanyPersonDTO extends BaseDataDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $nip,
        public readonly ?string $pesel,
    ) {
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
