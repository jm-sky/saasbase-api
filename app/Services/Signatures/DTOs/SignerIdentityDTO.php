<?php

namespace App\Services\Signatures\DTOs;

class SignerIdentityDTO
{
    public function __construct(
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public string $pesel,
        public ?string $trustedProfileId = null,
        public ?string $epuapUsername = null,
    ) {}
}
