<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Subscription\Enums\FeatureName;
use App\Domain\Tenant\Enums\TenantIntegrationType;
use App\Domain\Tenant\Models\Tenant;

class IntegrationLimitService
{
    public function __construct(
        private IntegrationCredentialService $credentialService
    ) {
    }

    public function shouldBypassApiLimits(string $tenantId, TenantIntegrationType $type): bool
    {
        return $this->credentialService->hasCustomCredentials($tenantId, $type);
    }

    public function shouldBypassIntegrationAccess(string $tenantId, TenantIntegrationType $type): bool
    {
        if ($this->credentialService->hasCustomCredentials($tenantId, $type)) {
            return true;
        }

        return $this->hasSubscriptionAccessToIntegration($tenantId, $type);
    }

    public function getEffectiveApiLimit(string $tenantId, TenantIntegrationType $type, FeatureName $limitFeature): ?int
    {
        if ($this->shouldBypassApiLimits($tenantId, $type)) {
            return null; // No limit for custom credentials
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return 0;
        }

        $subscription = $tenant->currentSubscription();

        if (!$subscription) {
            return 0;
        }

        $limit = $subscription->plan->getFeature($limitFeature);

        return is_numeric($limit) ? (int) $limit : 0;
    }

    public function canUseIntegration(string $tenantId, TenantIntegrationType $type): bool
    {
        if (!$this->credentialService->isIntegrationEnabled($tenantId, $type)) {
            return false;
        }

        return $this->shouldBypassIntegrationAccess($tenantId, $type);
    }

    private function hasSubscriptionAccessToIntegration(string $tenantId, TenantIntegrationType $type): bool
    {
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return false;
        }

        $subscription = $tenant->currentSubscription();

        if (!$subscription) {
            return false;
        }

        $featureName = $this->getIntegrationFeatureName($type);

        if (!$featureName) {
            return true; // No specific feature requirement
        }

        return $subscription->plan->hasFeature($featureName);
    }

    private function getIntegrationFeatureName(TenantIntegrationType $type): ?FeatureName
    {
        return match ($type) {
            TenantIntegrationType::Ksef      => FeatureName::KSEF_INTEGRATION,
            TenantIntegrationType::EDelivery => FeatureName::EDORECZENIA_INTEGRATION,
            default                          => null,
        };
    }

    public function getIntegrationStatus(string $tenantId, TenantIntegrationType $type): array
    {
        $hasCustomCredentials  = $this->credentialService->hasCustomCredentials($tenantId, $type);
        $isEnabled             = $this->credentialService->isIntegrationEnabled($tenantId, $type);
        $hasSubscriptionAccess = $this->hasSubscriptionAccessToIntegration($tenantId, $type);

        return [
            'integration_type'        => $type->value,
            'enabled'                 => $isEnabled,
            'has_custom_credentials'  => $hasCustomCredentials,
            'has_subscription_access' => $hasSubscriptionAccess,
            'can_use'                 => $this->canUseIntegration($tenantId, $type),
            'bypasses_limits'         => $hasCustomCredentials,
        ];
    }
}
