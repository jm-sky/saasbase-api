<?php

namespace App\Services\Signatures\DTOs;

class XmlSignatureDTO
{
    public function __construct(
        public bool $valid,
        public bool $trustedCA,
        public ?string $personFirstName,
        public ?string $personLastName,
        public ?string $personPESEL,
        public ?string $certificateIssuer,
        public ?string $certificateSerial,
        public ?string $certificateSubject,
        public ?string $certificateValidFrom,
        public ?string $certificateValidTo,
    ) {}
}
