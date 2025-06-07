<?php

namespace App\Services\Signatures\DTOs;

class XmlSignatureDTO
{
    public function __construct(
        public ?string $signingTime,
        public CertificateDTO $certificate,
        public SignerIdentityDTO $signerIdentity,
    ) {}
}
