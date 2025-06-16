<?php

namespace App\Services\KSeF\DTOs;

use Carbon\Carbon;

class QueryInvoiceResponseDTO
{
    public function __construct(
        public readonly Carbon $timestamp,
        public readonly string $referenceNumber,
        public readonly array $invoiceHeaderList,
        public readonly int $numberOfElements,
        public readonly int $pageOffset,
        public readonly int $pageSize
    ) {
    }
}
