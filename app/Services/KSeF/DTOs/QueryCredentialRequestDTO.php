<?php

namespace App\Services\KSeF\DTOs;

class QueryCredentialRequestDTO
{
    public function __construct(
        public readonly QueryCredentialCriteriaDTO $queryCriteria
    ) {
    }
}
