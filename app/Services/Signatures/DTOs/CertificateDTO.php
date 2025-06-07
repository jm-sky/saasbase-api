<?php

namespace App\Services\Signatures\DTOs;

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
