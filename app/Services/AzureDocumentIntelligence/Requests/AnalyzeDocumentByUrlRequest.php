<?php

namespace App\Services\AzureDocumentIntelligence\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AnalyzeDocumentByUrlRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $url,
        protected ?string $modelId = null,
        protected ?string $apiVersion = '2024-11-30'
    ) {
        $this->modelId    = $modelId ?? config('azure_doc_intel.model_id');
        $this->apiVersion = $apiVersion ?? config('azure_doc_intel.api_version');
    }

    public function resolveEndpoint(): string
    {
        return "/documentintelligence/documentModels/{$this->modelId}:analyze?api-version={$this->apiVersion}";
    }

    protected function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    protected function defaultBody(): mixed
    {
        return [
            'urlSource' => $this->url,
        ];
    }
}
