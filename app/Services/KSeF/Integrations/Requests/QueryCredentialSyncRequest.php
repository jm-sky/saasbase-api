<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use App\Services\KSeF\DTOs\QueryCredentialRequestDTO;

class QueryCredentialSyncRequest extends BaseKSeFRequest
{
    public function __construct(
        protected QueryCredentialRequestDTO $queryData
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/online/Query/Credential/Sync';
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

        if ($queryCriteria->credentialsIdentifier) {
            $criteria['credentialsIdentifier'] = $queryCriteria->credentialsIdentifier;
        }

        if ($queryCriteria->credentialsRole) {
            $criteria['credentialsRole'] = $queryCriteria->credentialsRole;
        }

        return $criteria;
    }
}
