<?php

namespace App\Services\KSeF\DTOs;

use Carbon\Carbon;

final class QueryCriteriaDTO
{
    public function __construct(
        public readonly ?string $subjectType = null,
        public readonly ?array $subjectToIdentifierList = null,
        public readonly ?array $subjectByIdentifierList = null,
        public readonly ?Carbon $invoicingDateFrom = null,
        public readonly ?Carbon $invoicingDateTo = null,
        public readonly ?Carbon $acquisitionTimestampThresholdFrom = null,
        public readonly ?Carbon $acquisitionTimestampThresholdTo = null,
        public readonly ?array $invoiceTypes = null,
        public readonly ?string $amountFrom = null,
        public readonly ?string $amountTo = null,
        public readonly ?string $currencyCode = null,
        public readonly ?bool $faP17Annotation = null
    ) {
    }
}
