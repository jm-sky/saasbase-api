<?php

namespace App\Services\KSeF\DTOs;

use Carbon\Carbon;

final class InvoiceStatusDTO
{
    public function __construct(
        public readonly string $invoiceNumber,
        public readonly string $ksefReferenceNumber,
        public readonly Carbon $acquisitionTimestamp
    ) {
    }
}
