<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Media;
use App\Domain\Subscription\Enums\FeatureName;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Resources\TenantQuotaResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TenantQuotaController extends Controller
{
    public function __invoke()
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();
        $tenant   = Tenant::with('subscription.plan.features')->findOrFail($tenantId);

        return new TenantQuotaResource($this->calculateQuota($tenant));
    }

    /**
     * Calculate tenant quota usage and limits.
     *
     * @return array{usedStorageGb: float, availableStorageGb: float, usedUsers: int, availableUsers: int|string}
     */
    private function calculateQuota(Tenant $tenant): array
    {
        $usedStorageBytes = Media::where('tenant_id', $tenant->id)->sum('size');
        $usedStorageMB    = round($usedStorageBytes / 1024 / 1024, 2);
        $usedUsers        = $tenant->users()->count();

        $features   = $tenant->subscription?->plan?->features->pluck('value', 'feature.name');
        $maxUsers   = $this->parseLimit($features[FeatureName::MAX_USERS->value] ?? '0');
        $maxStorage = (float) ($features[FeatureName::STORAGE_MB->value] ?? 0);

        return [
            'storage' => [
                'used'      => $usedStorageMB,
                'total'     => $maxStorage,
                'unit'      => 'MB',
            ],
            'users' => [
                'used'      => $usedUsers,
                'total'     => $maxUsers,
            ],
            'apiCalls' => [
                'used'      => 0,
                'total'     => 10000,
            ],
        ];
    }

    private function parseLimit(string $value): int|string
    {
        return match (strtolower($value)) {
            'unlimited', '-1' => 'unlimited',
            default => (int) $value,
        };
    }
}
