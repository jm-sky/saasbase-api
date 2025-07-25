<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Media;
use App\Domain\Subscription\Enums\FeatureName;
use App\Domain\Subscription\Resources\SubscriptionPlanResource;
use App\Domain\Tenant\DTOs\TenantQuotaDTO;
use App\Domain\Tenant\DTOs\TenantQuotaItemDTO;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Resources\TenantQuotaResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TenantSubscriptionController extends Controller
{
    public function quota(): TenantQuotaResource
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();
        $tenant   = Tenant::with('subscription.plan.features')->findOrFail($tenantId);

        return new TenantQuotaResource($this->calculateQuota($tenant));
    }

    public function currentPlan(): SubscriptionPlanResource
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();
        $tenant   = Tenant::with('subscription.plan.features')->findOrFail($tenantId);

        return new SubscriptionPlanResource($tenant->subscription->plan);
    }

    /**
     * Calculate tenant quota usage and limits.
     */
    private function calculateQuota(Tenant $tenant): TenantQuotaDTO
    {
        $usedStorageBytes = Media::where('tenant_id', $tenant->id)->sum('size');
        $usedStorageMB    = round($usedStorageBytes / 1024 / 1024, 2);
        $usedUsers        = $tenant->users()->count();

        $features   = $tenant->subscription?->plan?->features->pluck('value', 'feature.name');
        $maxUsers   = $this->parseLimit($features[FeatureName::MAX_USERS->value] ?? '0');
        $maxStorage = (float) ($features[FeatureName::STORAGE_MB->value] ?? 0);

        return new TenantQuotaDTO(
            storage: new TenantQuotaItemDTO(
                used: $usedStorageMB,
                total: $maxStorage,
                unit: 'MB',
            ),
            users: new TenantQuotaItemDTO(
                used: $usedUsers,
                total: $maxUsers,
            ),
            apiCalls: new TenantQuotaItemDTO(
                used: 0,
                total: 10000,
            ),
        );
    }

    private function parseLimit(string $value): int|string
    {
        return match (strtolower($value)) {
            'unlimited', '-1' => 'unlimited',
            default => (int) $value,
        };
    }
}
