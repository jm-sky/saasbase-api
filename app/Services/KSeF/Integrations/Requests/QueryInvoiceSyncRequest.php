<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use App\Services\KSeF\DTOs\QueryInvoiceRequestDTO;

class QueryInvoiceSyncRequest extends BaseKSeFRequest
{
    public function __construct(
        protected QueryInvoiceRequestDTO $queryData
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/online/Query/Invoice/Sync';
    }

    protected function defaultQuery(): array
    {
        return [
            'PageSize'   => $this->queryData->pageSize,
            'PageOffset' => $this->queryData->pageOffset,
        ];
    }

    protected function defaultBody(): array
    {
        return [
            'queryCriteria' => $this->buildQueryCriteria(),
        ];
    }

    protected function buildQueryCriteria(): array
    {
        $criteria      = [];
        $queryCriteria = $this->queryData->queryCriteria;

        if ($queryCriteria->subjectType) {
            $criteria['subjectType'] = $queryCriteria->subjectType;
        }

        if ($queryCriteria->subjectToIdentifierList) {
            $criteria['subjectToIdentifierList'] = $queryCriteria->subjectToIdentifierList;
        }

        if ($queryCriteria->subjectByIdentifierList) {
            $criteria['subjectByIdentifierList'] = $queryCriteria->subjectByIdentifierList;
        }

        if ($queryCriteria->invoicingDateFrom) {
            $criteria['invoicingDateFrom'] = $queryCriteria->invoicingDateFrom->format('Y-m-d');
        }

        if ($queryCriteria->invoicingDateTo) {
            $criteria['invoicingDateTo'] = $queryCriteria->invoicingDateTo->format('Y-m-d');
        }

        if ($queryCriteria->acquisitionTimestampThresholdFrom) {
            $criteria['acquisitionTimestampThresholdFrom'] = $queryCriteria->acquisitionTimestampThresholdFrom->toIso8601String();
        }

        if ($queryCriteria->acquisitionTimestampThresholdTo) {
            $criteria['acquisitionTimestampThresholdTo'] = $queryCriteria->acquisitionTimestampThresholdTo->toIso8601String();
        }

        if ($queryCriteria->invoiceTypes) {
            $criteria['invoiceTypes'] = $queryCriteria->invoiceTypes;
        }

        if ($queryCriteria->amountFrom) {
            $criteria['amountFrom'] = $queryCriteria->amountFrom;
        }

        if ($queryCriteria->amountTo) {
            $criteria['amountTo'] = $queryCriteria->amountTo;
        }

        if ($queryCriteria->currencyCode) {
            $criteria['currencyCode'] = $queryCriteria->currencyCode;
        }

        if (null !== $queryCriteria->faP17Annotation) {
            $criteria['faP17Annotation'] = $queryCriteria->faP17Annotation;
        }

        return $criteria;
    }
}
