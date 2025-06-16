<?php

namespace App\Services\KSeF\DTOs;

class QueryInvoiceRequestDTO
{
    public function __construct(
        public readonly QueryCriteriaDTO $queryCriteria,
        public readonly int $pageSize = 10,
        public readonly int $pageOffset = 0
    ) {
    }
}
