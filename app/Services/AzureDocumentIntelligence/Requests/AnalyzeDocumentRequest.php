<?php

namespace App\Services\AzureDocumentIntelligence\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStreamBody;

class AnalyzeDocumentRequest extends Request implements HasBody
{
    use HasStreamBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $modelId,
        protected string $filePath,
        protected string $apiVersion = '2023-10-31'
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/documentintelligence/documentModels/{$this->modelId}:analyze?api-version={$this->apiVersion}";
    }

    protected function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/pdf'];
    }

    protected function defaultBody(): mixed
    {
        return fopen($this->filePath, 'r');
    }
}
