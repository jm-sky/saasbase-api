<?php

namespace App\Services\AzureDocumentIntelligence;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for Azure Document Intelligence.
 */
class AzureConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return config('azure_doc_intel.endpoint');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Ocp-Apim-Subscription-Key' => config('azure_doc_intel.key'),
        ];
    }
}
