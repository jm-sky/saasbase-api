<?php

namespace App\Services\KSeF\DTOs;

final class QueryCredentialRequestDTO
{
    public function __construct(
        public readonly QueryCredentialCriteriaDTO $queryCriteria
    ) {
    }
}
