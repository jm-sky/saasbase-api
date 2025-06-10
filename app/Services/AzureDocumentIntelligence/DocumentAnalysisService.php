<?php

namespace App\Services\AzureDocumentIntelligence;

use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use App\Services\AzureDocumentIntelligence\Requests\AnalyzeDocumentRequest;
use App\Services\AzureDocumentIntelligence\Requests\GetAnalysisResultRequest;

/**
 * Service for handling Azure Document Intelligence analysis workflow.
 */
class DocumentAnalysisService
{
    protected AzureConnector $connector;

    public function __construct()
    {
        $this->connector = new AzureConnector();
    }

    public function analyze(string $filePath, ?string $modelId = null): array
    {
        $modelId = $modelId ?? config('azure_doc_intel.model_id');

        $uploadRequest = new AnalyzeDocumentRequest($modelId, $filePath);
        $response      = $this->connector->send($uploadRequest);

        if (!$response->successful()) {
            throw new AzureDocumentIntelligenceException('Failed to submit document to Azure.', context: ['response' => $response->json()]);
        }

        $operationLocation = $response->header('Operation-Location');

        sleep(5); // Simple polling, can be moved to a job with retry

        $pollRequest  = new GetAnalysisResultRequest($operationLocation);
        $pollResponse = $this->connector->send($pollRequest);

        return $pollResponse->json();
    }
}
