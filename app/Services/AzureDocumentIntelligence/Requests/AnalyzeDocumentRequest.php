<?php

namespace App\Services\AzureDocumentIntelligence\Requests;

use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStreamBody;

class AnalyzeDocumentRequest extends Request implements HasBody
{
    use HasStreamBody;

    protected Method $method = Method::POST;

    /**
     * @var string[]
     */
    protected array $allowedContentTypes = [
        'application/octet-stream',
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/tiff',
        'image/bmp',
        'image/heif',
        'text/html',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    public function __construct(
        protected string $filePath,
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
        $mimeType = mime_content_type($this->filePath);

        if (!in_array($mimeType, $this->allowedContentTypes)) {
            throw new AzureDocumentIntelligenceException('Invalid file type.', context: ['mime_type' => $mimeType]);
        }

        return ['Content-Type' => $mimeType];
    }

    protected function defaultBody(): mixed
    {
        return fopen($this->filePath, 'r');
    }
}
