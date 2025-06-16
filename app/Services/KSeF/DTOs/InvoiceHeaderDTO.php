<?php

namespace App\Services\KSeF\DTOs;

use Carbon\Carbon;

class InvoiceHeaderDTO
{
    public function __construct(
        public readonly Carbon $acquisitionTimestamp,
        public readonly string $currency,
        public readonly bool $faP17Annotation,
        public readonly string $gross,
        public readonly string $invoiceReferenceNumber,
        public readonly string $invoiceType,
        public readonly Carbon $invoicingDate,
        public readonly string $ksefReferenceNumber,
        public readonly string $net,
        public readonly string $vat,
        public readonly SubjectByDTO $subjectBy,
        public readonly SubjectToDTO $subjectTo,
        public readonly ?array $subjectToKList = null,
        public readonly ?array $subjectsAuthorizedList = null,
        public readonly ?array $subjectsOtherList = null,
        public readonly ?string $schemaVersion = null
    ) {
    }
}
