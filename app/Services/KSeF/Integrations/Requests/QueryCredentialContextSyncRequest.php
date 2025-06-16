<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class QueryCredentialContextSyncRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?string $contextNip = null,
        protected ?string $sourceIdentifier = null,
        protected ?string $targetIdentifier = null
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/online/Query/Credential/Context/Sync';
    }

    protected function defaultQuery(): array
    {
        $query = [];

        if ($this->contextNip) {
            $query['contextNip'] = $this->contextNip;
        }

        if ($this->sourceIdentifier) {
            $query['sourceIdentifier'] = $this->sourceIdentifier;
        }

        if ($this->targetIdentifier) {
            $query['targetIdentifier'] = $this->targetIdentifier;
        }

        return $query;
    }
}
