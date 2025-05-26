<?php

namespace App\Services\CompanyLookup\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Partner Data Transfer Object.
 */
class PartnerDTO extends BaseDataDTO
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
