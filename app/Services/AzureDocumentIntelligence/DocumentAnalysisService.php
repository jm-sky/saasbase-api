<?php

namespace App\Services\AzureDocumentIntelligence;

use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use App\Services\AzureDocumentIntelligence\Enums\DocumentAnalysisStatus;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use App\Services\AzureDocumentIntelligence\Requests\AnalyzeDocumentRequest;
use App\Services\AzureDocumentIntelligence\Requests\GetAnalysisResultRequest;
use Illuminate\Support\Facades\Cache;

/**
 * Service for handling Azure Document Intelligence analysis workflow.
 */
class DocumentAnalysisService
{
    protected AzureConnector $connector;

    protected const CACHE_TTL = 8 * 3600; // 8 hours cache

    protected const INITIAL_BACKOFF_TIME = 3; // 3 seconds

    protected const BACKOFF_TIME = 2; // 2 seconds

    public function __construct()
    {
        $this->connector = new AzureConnector();
    }

    public function analyze(string $filePath, ?string $modelId = null): DocumentAnalysisResult
    {
        $result = $this->analyzeRaw($filePath, $modelId);

        return DocumentAnalysisResult::fromArray($result);
    }

    public function analyzeRaw(string $filePath, ?string $modelId = null): array
    {
        $modelId = $modelId ?? config('azure_doc_intel.model_id');

        $uploadRequest = new AnalyzeDocumentRequest($modelId, $filePath);
        $response      = $this->connector->send($uploadRequest);

        if (!$response->successful()) {
            throw new AzureDocumentIntelligenceException('Failed to submit document to Azure.', context: ['response' => $response->json()]);
        }

        $operationLocation = $response->header('Operation-Location');

        // Simple polling, can be moved to a job with retry
        sleep(self::INITIAL_BACKOFF_TIME);

        while (true) {
            $pollRequest  = new GetAnalysisResultRequest($operationLocation);
            $pollResponse = $this->connector->send($pollRequest);
            $dto          = $pollResponse->dtoOrFail();
            $status       = $dto->status;

            if (DocumentAnalysisStatus::SUCCEEDED === $status) {
                break;
            }

            sleep(self::BACKOFF_TIME);
        }

        return $pollResponse->json();
    }

    /**
     * Analyze document with caching support.
     * Results are cached based on file content hash to avoid re-analyzing the same document.
     */
    public function analyzeWithCache(string $filePath, ?string $modelId = null, ?int $ttl = null, bool $force = false): DocumentAnalysisResult
    {
        $cacheKey = $this->generateCacheKey($filePath, $modelId);
        $ttl      = $ttl ?? self::CACHE_TTL;

        if ($force) {
            Cache::forget($cacheKey);
        }

        $result = Cache::remember($cacheKey, $ttl, function () use ($filePath, $modelId) {
            return $this->analyzeRaw($filePath, $modelId);
        });

        return DocumentAnalysisResult::fromArray($result);
    }

    /**
     * Generate a unique cache key for the document analysis.
     */
    protected function generateCacheKey(string $filePath, ?string $modelId = null): string
    {
        $fileHash = hash_file('sha256', $filePath);
        $modelId  = $modelId ?? config('azure_doc_intel.model_id');

        return "azure_doc_analysis:{$fileHash}:{$modelId}";
    }
}
