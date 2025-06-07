<?php

namespace App\Services\Signatures\DTOs;

use App\Services\Signatures\Enums\SignatureType;

class GenericSignaturesVerificationResultDTO
{
    public function __construct(
        public bool $valid,
        public SignatureType $type,
        /** @var array<GenericSignatureDetailsDTO> */
        public array $signatures,
        public ?string $error = null,
    ) {
    }
}
