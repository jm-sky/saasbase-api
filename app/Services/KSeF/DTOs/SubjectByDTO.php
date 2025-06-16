<?php

namespace App\Services\KSeF\DTOs;

class SubjectByDTO
{
    public function __construct(
        public readonly string $issuedByIdentifier,
        public readonly string $issuedByName,
        public readonly ?string $issuedToIdentifier = null,
        public readonly ?string $issuedToName = null
    ) {
    }
}
