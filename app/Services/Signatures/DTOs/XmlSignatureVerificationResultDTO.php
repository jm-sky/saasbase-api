<?php

namespace App\Services\Signatures\DTOs;

class XmlSignatureVerificationResultDTO
{
    public function __construct(
        public bool $valid,
        /** @var XmlSignatureDTO[] */
        public array $signatures,
    ) {
    }
}
