<?php

namespace App\Services\AzureDocumentIntelligence;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for Azure Document Intelligence.
 *
 * ## Custom Credentials Support
 *
 * This connector supports tenant-specific credentials through the IntegrationCredentialService.
 * To use custom credentials for a tenant:
 *
 * 1. Create a TenantIntegration record with mode='custom' and type='azureAi'
 * 2. Store encrypted credentials in the credentials field:
 *    ```php
 *    TenantIntegration::create([
 *        'tenant_id' => $tenantId,
 *        'type' => TenantIntegrationType::AzureAi,
 *        'mode' => TenantIntegrationMode::Custom,
 *        'enabled' => true,
 *        'credentials' => [
 *            'endpoint' => 'https://tenant-custom.cognitiveservices.azure.com/',
 *            'key' => 'tenant-specific-key',
 *            'region' => 'westus2'
 *        ]
 *    ]);
 *    ```
 *
 * 3. Use IntegrationCredentialService to resolve credentials:
 *    ```php
 *    $credentialService = app(IntegrationCredentialService::class);
 *    $credentials = $credentialService->getCredentials($tenantId, TenantIntegrationType::AzureAi);
 *
 *    // Create connector with custom credentials
 *    $connector = new AzureConnector($credentials);
 *    ```
 *
 * Benefits of custom credentials:
 * - Tenants bypass subscription API limits
 * - Dedicated Azure resources per tenant
 * - Separate billing and quota management
 * - Enhanced security isolation
 *
 * @see \App\Domain\Tenant\Services\IntegrationCredentialService
 * @see \App\Domain\Tenant\Services\IntegrationLimitService
 */
class AzureConnector extends Connector
{
    use AcceptsJson;

    protected ?array $customCredentials = null;

    public function __construct(?array $customCredentials = null)
    {
        $this->customCredentials = $customCredentials;
    }

    public function resolveBaseUrl(): string
    {
        return $this->customCredentials['endpoint'] ?? config('azure_doc_intel.endpoint');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Ocp-Apim-Subscription-Key' => $this->customCredentials['key'] ?? config('azure_doc_intel.key'),
        ];
    }
}
