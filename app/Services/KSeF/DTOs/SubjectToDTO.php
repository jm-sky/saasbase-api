<?php

namespace App\Services\KSeF\DTOs;

class SubjectToDTO
{
    public function __construct(
        public readonly string $issuedToIdentifier,
        public readonly string $issuedToName
    ) {
    }
}
