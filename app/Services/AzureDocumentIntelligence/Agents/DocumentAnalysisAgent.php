<?php

namespace App\Services\AzureDocumentIntelligence\Agents;

use App\Services\AzureDocumentIntelligence\DocumentAnalysisService;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use App\Services\AzureDocumentIntelligence\Enums\DocumentAnalysisStatus;

class DocumentAnalysisAgent
{
    public function __construct(
        protected DocumentAnalysisService $analysisService
    ) {
    }

    public function analyzeDocument(string $filePath, ?string $modelId = null): DocumentAnalysisResult
    {
        $rawResult = $this->analysisService->analyze($filePath, $modelId);

        return $this->mapToResult($rawResult);
    }

    protected function mapToResult(array $rawResult): DocumentAnalysisResult
    {
        // Map raw Azure response to our DTO
        return new DocumentAnalysisResult(
            status: DocumentAnalysisStatus::from($rawResult['status'] ?? 'failed'),
            fields: $rawResult['fields'] ?? null,
            error: $rawResult['error']['message'] ?? null
        );
    }
}
