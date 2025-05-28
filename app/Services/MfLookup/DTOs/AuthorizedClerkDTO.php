<?php

namespace App\Services\MfLookup\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * Authorized Clerk Data Transfer Object.
 */
class AuthorizedClerkDTO extends BaseDataDTO
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
