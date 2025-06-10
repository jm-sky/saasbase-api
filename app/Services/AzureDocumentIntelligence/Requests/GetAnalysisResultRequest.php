<?php

namespace App\Services\AzureDocumentIntelligence\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAnalysisResultRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $resultUrl)
    {
    }

    public function resolveEndpoint(): string
    {
        return $this->resultUrl;
    }
}
