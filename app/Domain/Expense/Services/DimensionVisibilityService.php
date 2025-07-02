<?php

namespace App\Domain\Expense\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Expense\Models\TenantDimensionConfiguration;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

class DimensionVisibilityService
{
    public function getEnabledDimensionsForTenant(?string $tenantId = null): SupportCollection
    {
        /** @var ?User $user */
        $user     = Auth::user();
        $tenantId = $tenantId ?? $user?->getTenantId();

        if (!$tenantId) {
            return collect();
        }

        // Get tenant configurations
        $configurations = TenantDimensionConfiguration::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->orderBy('display_order')
            ->get()
            ->keyBy('dimension_type')
        ;

        // Always include RTR (Transaction Type) first
        $enabledDimensions = collect([AllocationDimensionType::TRANSACTION_TYPE]);

        // Add configured dimensions
        foreach (AllocationDimensionType::cases() as $dimension) {
            if ($dimension->isConfigurable() && $configurations->has($dimension->value)) {
                $enabledDimensions->push($dimension);
            }
        }

        // Sort by display order
        return $enabledDimensions->sortBy(function ($dimension) use ($configurations) {
            if (AllocationDimensionType::TRANSACTION_TYPE === $dimension) {
                return 0; // Always first
            }

            return $configurations->get($dimension->value)?->display_order ?? 999;
        })->values();
    }

    public function getAllDimensionsForTenant(?string $tenantId = null): SupportCollection
    {
        /** @var ?User $user */
        $user     = Auth::user();
        $tenantId = $tenantId ?? $user?->getTenantId();

        if (!$tenantId) {
            return collect();
        }

        // Get tenant configurations
        $configurations = TenantDimensionConfiguration::where('tenant_id', $tenantId)
            ->orderBy('display_order')
            ->get()
            ->keyBy('dimension_type')
        ;

        // Include all dimensions with their configuration status
        $allDimensions = collect();

        foreach (AllocationDimensionType::cases() as $dimension) {
            $config = $configurations->get($dimension->value);

            $allDimensions->push([
                'dimension'         => $dimension,
                'is_enabled'        => $config?->is_enabled ?? $dimension->getDefaultEnabledState(),
                'display_order'     => $config?->display_order ?? $dimension->getDefaultDisplayOrder(),
                'is_always_visible' => $dimension->isAlwaysVisible(),
                'is_configurable'   => $dimension->isConfigurable(),
            ]);
        }

        return $allDimensions->sortBy('display_order')->values();
    }

    public function initializeDefaultConfigurationForTenant(string $tenantId): void
    {
        foreach (AllocationDimensionType::cases() as $dimension) {
            // Only create configurations for configurable dimensions
            // RTR (Transaction Type) is always visible and doesn't need configuration
            if ($dimension->isConfigurable()) {
                TenantDimensionConfiguration::create([
                    'tenant_id'      => $tenantId,
                    'dimension_type' => $dimension,
                    'is_enabled'     => $dimension->getDefaultEnabledState(),
                    'display_order'  => $dimension->getDefaultDisplayOrder(),
                ]);
            }
        }
    }

    public function updateDimensionConfiguration(
        string $tenantId,
        AllocationDimensionType $dimension,
        bool $isEnabled,
        ?int $displayOrder = null
    ): void {
        // Cannot configure RTR (Transaction Type) - it's always visible
        if (!$dimension->isConfigurable()) {
            throw new \InvalidArgumentException("Dimension {$dimension->value} is not configurable");
        }

        TenantDimensionConfiguration::updateOrCreate(
            [
                'tenant_id'      => $tenantId,
                'dimension_type' => $dimension,
            ],
            [
                'is_enabled'    => $isEnabled,
                'display_order' => $displayOrder ?? $dimension->getDefaultDisplayOrder(),
            ]
        );
    }

    public function bulkUpdateDimensionConfigurations(string $tenantId, array $configurations): void
    {
        foreach ($configurations as $config) {
            $dimension = AllocationDimensionType::from($config['dimension_type']);

            $this->updateDimensionConfiguration(
                $tenantId,
                $dimension,
                $config['is_enabled'],
                $config['display_order'] ?? null
            );
        }
    }

    public function getDimensionConfiguration(string $tenantId, AllocationDimensionType $dimension): ?TenantDimensionConfiguration
    {
        return TenantDimensionConfiguration::where('tenant_id', $tenantId)
            ->where('dimension_type', $dimension)
            ->first()
        ;
    }

    public function isDimensionEnabledForTenant(string $tenantId, AllocationDimensionType $dimension): bool
    {
        // RTR (Transaction Type) is always enabled
        if ($dimension->isAlwaysVisible()) {
            return true;
        }

        $config = $this->getDimensionConfiguration($tenantId, $dimension);

        return $config?->is_enabled ?? $dimension->getDefaultEnabledState();
    }

    public function resetToDefaults(string $tenantId): void
    {
        // Delete existing configurations
        TenantDimensionConfiguration::where('tenant_id', $tenantId)->delete();

        // Recreate with defaults
        $this->initializeDefaultConfigurationForTenant($tenantId);
    }
}
