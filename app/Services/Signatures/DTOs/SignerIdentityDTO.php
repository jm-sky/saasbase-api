<?php

namespace App\Services\Signatures\DTOs;

class SignerIdentityDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $pesel,
        public ?string $middleName = null,
        public ?string $trustedProfileId = null,
        public ?string $epuapUsername = null,
    ) {
    }
}
