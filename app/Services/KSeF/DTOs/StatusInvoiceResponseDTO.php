<?php

namespace App\Services\KSeF\DTOs;

use Carbon\Carbon;

final class StatusInvoiceResponseDTO
{
    public function __construct(
        public readonly Carbon $timestamp,
        public readonly string $referenceNumber,
        public readonly int $processingCode,
        public readonly string $processingDescription,
        public readonly string $elementReferenceNumber,
        public readonly ?InvoiceStatusDTO $invoiceStatus = null
    ) {
    }
}
