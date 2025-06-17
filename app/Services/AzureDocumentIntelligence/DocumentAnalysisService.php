<?php

namespace App\Services\AzureDocumentIntelligence;

use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use App\Services\AzureDocumentIntelligence\Enums\DocumentAnalysisStatus;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use App\Services\AzureDocumentIntelligence\Requests\AnalyzeDocumentByUrlRequest;
use App\Services\AzureDocumentIntelligence\Requests\AnalyzeDocumentRequest;
use App\Services\AzureDocumentIntelligence\Requests\GetAnalysisResultRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

    public function analyze(string $filePath): DocumentAnalysisResult
    {
        if ($this->isUrl($filePath)) {
            $result = $this->analyzeByUrlInternal($filePath);
        } else {
            $result = $this->analyzeByContentInternal($filePath);
        }

        return DocumentAnalysisResult::fromArray($result);
    }

    /**
     * Analyze document with caching support.
     * Results are cached based on file content hash to avoid re-analyzing the same document.
     */
    public function analyzeWithCache(string $filePath, ?int $ttl = null, bool $force = false): DocumentAnalysisResult
    {
        $cacheKey = $this->generateCacheKey($filePath);
        $ttl      = $ttl ?? self::CACHE_TTL;

        if ($force) {
            Cache::forget($cacheKey);
        }

        /* @var array $result */
        $result = Cache::remember($cacheKey, $ttl, function () use ($filePath) {
            if ($this->isUrl($filePath)) {
                return $this->analyzeByUrlInternal($filePath);
            }

            return $this->analyzeByContentInternal($filePath);
        });

        return DocumentAnalysisResult::fromArray($result);
    }

    public function analyzeByContentInternal(string $filePath, ?string $modelId = null): array
    {
        $uploadRequest = new AnalyzeDocumentRequest($filePath);
        $response      = $this->connector->send($uploadRequest);

        if (!$response->successful()) {
            throw new AzureDocumentIntelligenceException('Failed to submit document to Azure.', context: ['response' => $response->json()]);
        }

        $operationLocation = $response->header('Operation-Location');

        return $this->pollForAnalysisResult($operationLocation);
    }

    public function analyzeByUrlInternal(string $url): array
    {
        $uploadRequest = new AnalyzeDocumentByUrlRequest($url);
        $response      = $this->connector->send($uploadRequest);

        if (!$response->successful()) {
            throw new AzureDocumentIntelligenceException('Failed to submit document to Azure.', context: ['response' => $response->json()]);
        }

        $operationLocation = $response->header('Operation-Location');

        return $this->pollForAnalysisResult($operationLocation);
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

    protected function pollForAnalysisResult(string $operationLocation): array
    {
        sleep(self::INITIAL_BACKOFF_TIME);

        while (true) {
            $pollRequest  = new GetAnalysisResultRequest($operationLocation);
            $pollResponse = $this->connector->send($pollRequest);
            $dto          = $pollResponse->dtoOrFail();
            $status       = $dto->status;

            if (DocumentAnalysisStatus::SUCCEEDED === $status) {
                break;
            }

            if (DocumentAnalysisStatus::FAILED === $status) {
                throw new AzureDocumentIntelligenceException('Failed to analyze document.', context: ['response' => $pollResponse->json()]);
            }

            sleep(self::BACKOFF_TIME);
        }

        return $pollResponse->json();
    }

    protected function isUrl(string $filePath): bool
    {
        return Str::startsWith($filePath, 'http://') || Str::startsWith($filePath, 'https://');
    }
}
