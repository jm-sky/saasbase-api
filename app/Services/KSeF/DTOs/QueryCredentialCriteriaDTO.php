<?php

namespace App\Services\KSeF\DTOs;

class QueryCredentialCriteriaDTO
{
    public function __construct(
        public readonly ?array $credentialsIdentifier = null,
        public readonly ?array $credentialsRole = null
    ) {
    }
}
