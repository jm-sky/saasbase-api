<?php

namespace App\Services\AzureDocumentIntelligence;

use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use App\Services\AzureDocumentIntelligence\Enums\DocumentAnalysisStatus;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use App\Services\AzureDocumentIntelligence\Requests\AnalyzeDocumentByUrlRequest;
use App\Services\AzureDocumentIntelligence\Requests\AnalyzeDocumentRequest;
use App\Services\AzureDocumentIntelligence\Requests\GetAnalysisResultRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for handling Azure Document Intelligence analysis workflow.
 *
 * ## Custom Credentials Usage
 *
 * This service supports tenant-specific Azure credentials to bypass subscription limits.
 *
 * ### Basic Usage with Custom Credentials
 *
 * ```php
 * use App\Domain\Tenant\Services\IntegrationCredentialService;
 * use App\Domain\Tenant\Enums\TenantIntegrationType;
 *
 * // Resolve credentials for tenant (custom or global)
 * $credentialService = app(IntegrationCredentialService::class);
 * $credentials = $credentialService->getCredentials($tenantId, TenantIntegrationType::AzureAi);
 *
 * // Create service with custom credentials
 * $service = new DocumentAnalysisService($credentials);
 * $result = $service->analyze($filePath);
 * ```
 *
 * ### Check Limits and Permissions
 *
 * ```php
 * use App\Domain\Tenant\Services\IntegrationLimitService;
 *
 * $limitService = app(IntegrationLimitService::class);
 *
 * // Check if tenant can use this integration
 * if (!$limitService->canUseIntegration($tenantId, TenantIntegrationType::AzureAi)) {
 *     throw new UnauthorizedException('Azure AI integration not available for this tenant');
 * }
 *
 * // Check if tenant bypasses limits (has custom credentials)
 * $bypassesLimits = $limitService->shouldBypassApiLimits($tenantId, TenantIntegrationType::AzureAi);
 * ```
 *
 * ### Setting Up Custom Credentials
 *
 * Tenants can configure custom Azure credentials through the TenantIntegration API:
 *
 * ```json
 * POST /api/tenants/{tenant}/integrations
 * {
 *   "type": "azureAi",
 *   "mode": "custom",
 *   "enabled": true,
 *   "credentials": {
 *     "endpoint": "https://tenant-azure.cognitiveservices.azure.com/",
 *     "key": "tenant-specific-subscription-key",
 *     "region": "westus2"
 *   }
 * }
 * ```
 *
 * @see \App\Domain\Tenant\Services\IntegrationCredentialService
 * @see \App\Domain\Tenant\Services\IntegrationLimitService
 */
class DocumentAnalysisService
{
    protected AzureConnector $connector;

    protected const CACHE_TTL = 8 * 3600; // 8 hours cache

    protected const INITIAL_BACKOFF_TIME = 3; // 3 seconds

    protected const BACKOFF_TIME = 2; // 2 seconds

    protected bool $useMock = false;

    public function __construct(?array $customCredentials = null)
    {
        $this->connector = new AzureConnector($customCredentials);
        $this->useMock   = config('azure_doc_intel.use_mock', false);
    }

    public function analyze(string $filePath): DocumentAnalysisResult
    {
        if ($this->useMock) {
            return $this->returnMockResult();
        }

        if ($this->isUrl($filePath)) {
            $result = $this->analyzeByUrlInternal($filePath);
        } else {
            $result = $this->analyzeByContentInternal($filePath);
        }

        return DocumentAnalysisResult::fromAzureArray($result);
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

        return DocumentAnalysisResult::fromAzureArray($result);
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
            Log::error('Failed to submit document to Azure.', ['response' => $response->json()]);

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

    protected function returnMockResult(): DocumentAnalysisResult
    {
        $json = json_decode(file_get_contents(storage_path('app/azure_doc_intel.json')), true);

        return DocumentAnalysisResult::fromAzureArray($json);
    }
}
