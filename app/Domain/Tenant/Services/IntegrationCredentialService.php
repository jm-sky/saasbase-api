<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Enums\TenantIntegrationMode;
use App\Domain\Tenant\Enums\TenantIntegrationType;
use App\Domain\Tenant\Models\TenantIntegration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class IntegrationCredentialService
{
    private const CACHE_PREFIX = 'tenant_integration_credentials';

    private const CACHE_TTL = 900; // 15 minutes

    public function getCredentials(string $tenantId, TenantIntegrationType $type): ?array
    {
        $cacheKey = $this->getCacheKey($tenantId, $type);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId, $type) {
            return $this->resolveCredentials($tenantId, $type);
        });
    }

    public function clearCredentialsCache(string $tenantId, TenantIntegrationType $type): void
    {
        $cacheKey = $this->getCacheKey($tenantId, $type);
        Cache::forget($cacheKey);
    }

    public function clearAllCredentialsCache(string $tenantId): void
    {
        foreach (TenantIntegrationType::cases() as $type) {
            $this->clearCredentialsCache($tenantId, $type);
        }
    }

    private function resolveCredentials(string $tenantId, TenantIntegrationType $type): ?array
    {
        $integration = TenantIntegration::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('enabled', true)
            ->first()
        ;

        if (!$integration) {
            return $this->getGlobalCredentials($type);
        }

        return match ($integration->mode) {
            TenantIntegrationMode::Custom => $integration->credentials,
            TenantIntegrationMode::Shared => $this->getGlobalCredentials($type),
        };
    }

    private function getGlobalCredentials(TenantIntegrationType $type): ?array
    {
        $configKey = "integrations.{$type->value}";
        $config    = Config::get($configKey);

        if (!$config || !is_array($config)) {
            return null;
        }

        return $config;
    }

    private function getCacheKey(string $tenantId, TenantIntegrationType $type): string
    {
        return self::CACHE_PREFIX . ":{$tenantId}:{$type->value}";
    }

    public function hasCustomCredentials(string $tenantId, TenantIntegrationType $type): bool
    {
        $integration = TenantIntegration::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('enabled', true)
            ->first()
        ;

        return $integration && TenantIntegrationMode::Custom === $integration->mode;
    }

    public function isIntegrationEnabled(string $tenantId, TenantIntegrationType $type): bool
    {
        $integration = TenantIntegration::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->first()
        ;

        return $integration && $integration->enabled;
    }
}
