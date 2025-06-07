<?php

namespace App\Services\Signatures\DTOs;

class XmlSignatureVerificationResultDTO
{
    public function __construct(
        public bool $valid,
        /** @var XmlSignatureDTO[] */
        public array $signatures,
    ) {}
}

class XmlSignatureDTO
{
    public function __construct(
        public ?string $signingTime,
        public CertificateDTO $certificate,
        public SignerIdentityDTO $signerIdentity,
    ) {}
}

class CertificateDTO
{
    public function __construct(
        public string $issuer,
        public string $serialNumber,
        public ?string $validFrom = null,
        public ?string $validTo = null,
        public ?string $subject = null,
    ) {}
}

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
