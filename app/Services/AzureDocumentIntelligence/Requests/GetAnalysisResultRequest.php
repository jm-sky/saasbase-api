<?php

namespace App\Services\AzureDocumentIntelligence\Requests;

use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

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

    public function createDtoFromResponse(Response $response): DocumentAnalysisResult
    {
        return DocumentAnalysisResult::fromAzureArray($response->json());
    }
}
