<?php

namespace App\Services\Signatures\DTOs;

class GenericSignatureDetailsDTO
{
    public function __construct(
        public bool $valid,
        public bool $trustedCA,
        public ?SignerIdentityDTO $signerIdentity = null,
        public ?CertificateDTO $certificate = null,
    ) {
    }
}
